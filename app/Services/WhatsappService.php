<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendText(string $to, string $body): bool
    {
        $token = config('whatsapp.token');
        $phoneNumberId = config('whatsapp.phone_number_id');

        if (!$token || !$phoneNumberId) {
            Log::info('WhatsApp skipped, credentials missing', ['to' => $to, 'body' => $body]);
            return false;
        }

        $to = preg_replace('/\D+/', '', $to);
        if (str_starts_with($to, '0')) {
            $to = '234' . substr($to, 1);
        }

        $response = Http::withToken($token)->post(
            'https://graph.facebook.com/' . config('whatsapp.graph_version') . '/' . $phoneNumberId . '/messages',
            [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $body],
            ]
        );

        if ($response->failed()) {
            Log::warning('WhatsApp send failed', ['to' => $to, 'body' => $response->body()]);
            return false;
        }

        return true;
    }
}