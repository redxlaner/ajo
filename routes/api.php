<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/webhooks/whatsapp', function (Request $request) {
    $mode = $request->query('hub_mode');
    $token = $request->query('hub_verify_token');
    $challenge = $request->query('hub_challenge');

    if ($mode === 'subscribe' && $token === config('whatsapp.verify_token')) {
        return response($challenge, 200);
    }

    return response('Forbidden', 403);
});

Route::post('/webhooks/whatsapp', function (Request $request) {
    $payload = $request->all();
    Log::info('WhatsApp webhook', $payload);

    $message = data_get($payload, 'entry.0.changes.0.value.messages.0');
    if (!$message) {
        return response('ok', 200);
    }

    $from = $message['from'] ?? null;
    $text = strtolower(trim($message['text']['body'] ?? ''));
    if (!$from || $text === '') {
        return response('ok', 200);
    }

    $digits = preg_replace('/\D+/', '', $from);
    $local = str_starts_with($digits, '234') ? '0' . substr($digits, 3) : $digits;

    $user = \App\Models\User::query()
        ->where('phone', $from)
        ->orWhere('phone', $local)
        ->orWhere('phone', $digits)
        ->first();

    $reply = 'Link this number in Ajo profile, then send help.';

    if ($user && $user->phone_verified_at) {
        if (in_array($text, ['help', '/help', 'menu', '/ajo'])) {
            $reply = "Ajo commands:\nbalance — pledges and vaults\ndue — what you owe\ninvite — your invite link\nhelp — this list";
        } elseif ($text === 'balance') {
            $pledges = \App\Models\ProjectPledge::where('user_id', $user->id)->where('status', 'active')->count();
            $reply = "Ajo balance\nActive pledges: {$pledges}\nOpen the app for vault totals.";
        } elseif ($text === 'due') {
            $due = \App\Models\ProjectPledge::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereDate('next_due_on', '<=', now()->toDateString())
                ->sum('amount');
            $reply = 'Ajo due now: ₦' . number_format($due, 0);
        } elseif ($text === 'invite') {
            $code = $user->invite_code;
            if (!$code) {
                $user->update([
                    'invite_code' => strtoupper(substr(md5(uniqid((string) $user->id, true)), 0, 8)),
                ]);
                $code = $user->fresh()->invite_code;
            }
            $reply = 'Your Ajo invite: ' . route('invite.user', $code);
        } else {
            $reply = 'Send help to see Ajo commands.';
        }
    }

    app(\App\Services\WhatsAppService::class)->sendText($from, $reply);

    return response('ok', 200);
});