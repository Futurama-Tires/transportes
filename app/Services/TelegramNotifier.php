<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public function send(string $text, ?string $chatId = null, array $options = []): bool
    {
        $token  = config('services.telegram.bot_token');
        $chatId = $chatId ?: config('services.telegram.default_chat_id');
        $apiUrl = rtrim(config('services.telegram.api_url'), '/');

        if (!$token || !$chatId) {
            Log::warning('Telegram: faltan credenciales (bot_token/chat_id).');
            return false;
        }

        $payload = array_merge([
            'chat_id'                  => $chatId,
            'text'                     => $text,
            'parse_mode'               => $options['parse_mode'] ?? 'HTML', // HTML es práctico
            'disable_web_page_preview' => true,
        ], $options);

        $response = Http::asForm()->post("{$apiUrl}/bot{$token}/sendMessage", $payload);

        $ok = $response->ok() && data_get($response->json(), 'ok') === true;

        if (!$ok) {
            Log::error('Telegram: fallo al enviar', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $ok;
    }
}
