<?php

namespace App\Console\Commands;

use App\Models\ProjectPledge;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendPledgeDueReminders extends Command
{
    protected $signature = 'ajo:pledge-due-reminders';

    protected $description = 'WhatsApp members whose pledge is due today';

    public function handle(WhatsAppService $whatsapp): int
    {
        $pledges = ProjectPledge::with(['user', 'project'])
            ->where('status', 'active')
            ->whereDate('next_due_on', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('due_reminded_at')
                    ->orWhereDate('due_reminded_at', '<', now()->toDateString());
            })
            ->get();

        foreach ($pledges as $pledge) {
            $user = $pledge->user;
            if (!$user || !$user->phone || !$user->phone_verified_at || $user->whatsapp_opt_in !== 'on') {
                continue;
            }

            $whatsapp->sendText(
                $user->phone,
                'Ajo: ₦' . number_format($pledge->amount, 0) . ' is due today for ' . $pledge->project->title . '.'
            );

            $pledge->update(['due_reminded_at' => now()]);
        }

        $this->info('Checked ' . $pledges->count() . ' due pledges.');

        return self::SUCCESS;
    }
}