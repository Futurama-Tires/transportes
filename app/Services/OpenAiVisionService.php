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
- "importe" es el total pagado (con impuestos).
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

    protected function chatWithImage(string $prompt, string $localPath): string
    {
        $mime = $this->guessMime($localPath); // ej. image/jpeg
        $b64  = base64_encode(file_get_contents($localPath));
        $dataUrl = "data:{$mime};base64,{$b64}";

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
                        ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
                    ],
                ],
            ],
        ];

        $resp = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if (!$resp->successful()) {
            throw new \RuntimeException("OpenAI error: {$resp->status()} {$resp->body()}");
        }

        $json = $resp->json();
        return $json['choices'][0]['message']['content'] ?? '';
    }

    protected function safeJson(string $content, array $fallback): array
    {
        // Quita fences ```json ... ```
        $clean = trim($content);
        if (Str::startsWith($clean, '```')) {
            $clean = preg_replace('/^```(?:json)?/i', '', $clean);
            $clean = preg_replace('/```$/', '', $clean);
        }
        // Extrae primer bloque JSON
        if (preg_match('/\{.*\}/s', $clean, $m)) {
            $clean = $m[0];
        }
        $data = json_decode($clean, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return $fallback;
        }
        // Mezcla con fallback para garantizar llaves
        return array_merge($fallback, $data);
    }

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
