<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenAiVisionService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.openai.key');
        $this->baseUrl = rtrim(config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
    }

    public function extractTicketData(string $localPath): array
    {
        $prompt = <<<PROMPT
Eres un extractor de datos de tickets de combustible en México.
Devuelve exclusivamente un JSON válido (sin comentarios ni texto adicional) con el siguiente esquema:

{
  "fecha": "YYYY-MM-DD",
  "tipo_combustible": "Magna|Diesel|Premium|null",
  "litros": number|null,
  "precio_por_litro": number|null,
  "importe": number|null,
  "fuente": "ticket"
}

Reglas:
- Si un dato no está claro, pon null.
- "fecha" debe normalizarse a "YYYY-MM-DD" (si no hay año, asume el del día actual).
- "importe" debe ser el TOTAL FINAL pagado, con impuestos incluidos.
  • Si el ticket muestra SUBTOTAL, IMPORTE y TOTAL → elige TOTAL, o bien en su defecto, elige el importe mas grande entre esos tres .
  • Ignora subtotales o importes parciales.
- "precio_por_litro" es el unitario.
- Solo JSON. Nada de explicación.

PROMPT;

        $content = $this->chatWithImage($prompt, $localPath);
        return $this->safeJson($content, [
            'fecha' => null,
            'tipo_combustible' => null,
            'litros' => null,
            'precio_por_litro' => null,
            'importe' => null,
            'fuente' => 'ticket',
        ]);
    }

    public function extractVoucherAmount(string $localPath): array
    {
        $prompt = <<<PROMPT
Eres un extractor de voucher/comprobante de pago.
Devuelve exclusivamente un JSON válido:

{
  "importe": number|null,
  "fuente": "voucher"
}

- "importe" es el total cobrado en el voucher.
- Si no hay certeza, usa null.
- Solo JSON. Sin texto adicional.
PROMPT;

        $content = $this->chatWithImage($prompt, $localPath);
        return $this->safeJson($content, [
            'importe' => null,
            'fuente' => 'voucher',
        ]);
    }

    public function extractOdometer(string $localPath): array
    {
        $prompt = <<<PROMPT
Eres un extractor de odómetro (odometer) de un tablero de vehículo.
Devuelve exclusivamente un JSON válido:

{
  "km_final": integer|null,
  "fuente": "odometro"
}

- "km_final" debe ser un entero (kilómetros). Si es ilegible, usa null.
- Solo JSON. Sin texto adicional.
PROMPT;

        $content = $this->chatWithImage($prompt, $localPath);
        return $this->safeJson($content, [
            'km_final' => null,
            'fuente' => 'odometro',
        ]);
    }

    // ----------------- Helpers -----------------

    /**
     * Envía imagen por data URL (base64) después de redimensionar/recomprimir a JPG
     * y consulta Chat Completions. Incluye timeouts amplios y reintentos.
     */
    protected function chatWithImage(string $prompt, string $localPath): string
    {
        // 1) Downscale + recomprimir a JPEG antes de base64 (mejora estabilidad y peso)
        $tmp = $this->downscaleForOcr($localPath, 2048, 85); // 2048 px lado mayor, JPG 85%
        $mime = 'image/jpeg';
        $b64  = base64_encode(@file_get_contents($tmp));
        if ($b64 === false) {
            // fallback duro si no se pudo leer (raro, pero evita excepción silenciosa)
            $b64 = base64_encode(@file_get_contents($localPath)) ?: '';
        }
        $dataUrl = "data:{$mime};base64,{$b64}";

        // 2) Payload Chat Completions con imagen embebida
        $payload = [
            'model' => 'gpt-4o-mini',
            'temperature' => 0,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Eres un asistente estricto que SOLO devuelve JSON válido según se solicite.',
                ],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        // Puedes pasar image_url como string o como objeto con {url:...}
                        ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
                    ],
                ],
            ],
            'max_tokens' => 300, // el JSON esperado es pequeño
        ];

        try {
            // 3) HTTP con timeout amplio, connect timeout y reintentos con backoff
            $resp = Http::withToken($this->apiKey)
                ->timeout(120)           // total response timeout
                ->connectTimeout(10)     // handshake/connection timeout
                ->retry(3, 1500, function ($exception, $request) {
                    // Reintentar en: timeouts/errores de red (exception) o 429/5xx
                    if ($exception) return true;
                    $res = $request->response ?? null;
                    if (!$res) return true; // por si no hubo respuesta
                    $code = $res->status();
                    return $code === 429 || $code >= 500;
                })
                ->post("{$this->baseUrl}/chat/completions", $payload);

            if (!$resp->successful()) {
                $rid = $resp->header('x-request-id') ?: 'n/a';
                throw new \RuntimeException("OpenAI error: {$resp->status()} [req:$rid] {$resp->body()}");
            }

            $json = $resp->json();
            return $json['choices'][0]['message']['content'] ?? '';
        } finally {
            // 4) Limpia temporal
            if (isset($tmp) && is_file($tmp)) @unlink($tmp);
        }
    }

    /**
     * Sanitiza y decodifica JSON. Soporta respuestas con fences y texto adicional.
     */
    protected function safeJson(string $content, array $fallback): array
    {
        $clean = trim($content);

        // Quitar fences tipo ```json ... ```
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean ?? '');
        $clean = preg_replace('/\s*```$/', '', $clean ?? '');

        // Extraer el primer objeto JSON
        if (preg_match('/\{.*\}/s', $clean ?? '', $m)) {
            $clean = $m[0];
        }

        $data = json_decode($clean ?? '', true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return $fallback;
        }

        // Garantiza llaves esperadas
        return array_replace($fallback, $data);
    }

    /**
     * Redimensiona (lado mayor = $maxSide) y re-codifica a JPEG (quality $jpegQuality).
     * Si no hay GD o no se puede procesar, devuelve el path original.
     */
    protected function downscaleForOcr(string $path, int $maxSide = 2048, int $jpegQuality = 85): string
    {
        // Si GD no está disponible, no intentamos
        if (!function_exists('imagecreatetruecolor')) {
            return $path;
        }

        $info = @getimagesize($path);
        if (!$info) return $path;

        [$w, $h, $type] = $info;
        $max = max($w, $h);
        $scale = $max > $maxSide ? ($maxSide / $max) : 1.0;

        // ¿Hace falta redimensionar o re-codificar?
        $needResize = $scale < 1.0;
        $needReencode = in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_JPEG], true); // re-encode a JPG suele bajar peso

        if (!$needResize && !$needReencode) {
            return $path;
        }

        // Cargar imagen fuente
        switch ($type) {
            case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($path); break;
            case IMAGETYPE_PNG:  $src = @imagecreatefrompng($path);  break;
            case IMAGETYPE_WEBP: $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null; break;
            default: $src = null; break;
        }
        if (!$src) return $path;

        $nw = $needResize ? (int) round($w * $scale) : $w;
        $nh = $needResize ? (int) round($h * $scale) : $h;

        $dst = imagecreatetruecolor($nw, $nh);

        // Fondo blanco para evitar negros en PNG con transparencia
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $tmp = tempnam(sys_get_temp_dir(), 'ocr_') . '.jpg';
        imagejpeg($dst, $tmp, max(1, min(100, $jpegQuality)));

        imagedestroy($src);
        imagedestroy($dst);

        // Si por alguna razón falló la escritura, regresamos original
        if (!is_file($tmp) || filesize($tmp) === 0) {
            return $path;
        }

        return $tmp;
    }

    /**
     * Conservada por compatibilidad, aunque ahora re-codificamos a JPG.
     */
    protected function guessMime(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            'heic'        => 'image/heic',
            default       => 'image/jpeg',
        };
    }
}

