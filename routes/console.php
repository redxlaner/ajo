<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run missed contribution check on the 1st of every month at 1:00 AM
Schedule::command('contributions:check-missed')->monthlyOn(1, '01:00');

// Remind members about a meeting about 24 hours before it starts
Schedule::call(function () {
    $meetings = \App\Models\TeamMeeting::with('community')
        ->where('status', 'scheduled')
        ->whereNull('reminded_at')
        ->whereBetween('starts_at', [now()->addHours(23), now()->addHours(25)])
        ->get();

    foreach ($meetings as $meeting) {
        $members = $meeting->community
            ->members()
            ->where('status', 'active')
            ->with('user')
            ->get();

        foreach ($members as $member) {
            if ($member->user) {
                $member->user->notify(new \App\Notifications\TeamMeetingScheduled($meeting, true));

                $user = $member->user;
                if ($user->phone && $user->phone_verified_at && $user->whatsapp_opt_in === 'on') {
                    app(\App\Services\WhatsAppService::class)->sendText(
                        $user->phone,
                        'Ajo: ' . $meeting->title . ' starts ' . $meeting->starts_at->format('D j M, g:i A') . '.'
                    );
                }
            }
        }

        $meeting->update(['reminded_at' => now()]);
    }
})->hourly();

// Recurring project pledges: due today, late after 3 days. Once per day.
Schedule::call(function () {
    $pledges = \App\Models\ProjectPledge::with(['user', 'project.community'])
        ->where('status', 'active')
        ->whereDate('next_due_on', '<=', now()->toDateString())
        ->get();

    foreach ($pledges as $pledge) {
        if (!$pledge->user || !$pledge->project) {
            continue;
        }

        $late = $pledge->next_due_on->copy()->startOfDay()->diffInDays(now()->startOfDay()) >= 3;

        if ($late && $pledge->late_reminded_at && $pledge->late_reminded_at->isToday()) {
            continue;
        }
        if (!$late && $pledge->due_reminded_at && $pledge->due_reminded_at->isToday()) {
            continue;
        }

        $pledge->user->notify(new \App\Notifications\PledgeDue($pledge, $late));

        $user = $pledge->user;
            if ($user->phone && $user->phone_verified_at && $user->whatsapp_opt_in === 'on') {
                $label = $late ? 'is late' : 'is due today';
                app(\App\Services\WhatsAppService::class)->sendText(
                    $user->phone,
                    'Ajo: ₦' . number_format($pledge->amount, 0) . ' ' . $label . ' for ' . $pledge->project->title . '.'
                );
            }

        $pledge->update($late
            ? ['late_reminded_at' => now()]
            : ['due_reminded_at' => now()]
        );
    }
})->dailyAt('08:00');