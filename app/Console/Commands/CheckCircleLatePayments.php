<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\ProjectPayoutSlot;

class CheckCircleLatePayments extends Command
{
    protected $signature = 'circles:check-late';
    protected $description = 'Check rotational projects for late payments and apply rules';

    public function handle()
    {
        $notificationClass = 'App\\Notifications\\CirclePaymentLate';

        $projects = Project::where('project_type', 'rotational')
            ->where('status', 'active')
            ->with('payoutSlots')
            ->get();

        foreach ($projects as $project) {
            $today = now()->startOfDay();
            $grace = $project->grace_days ?? 3;

            foreach ($project->payoutSlots as $slot) {
                if ($slot->status !== 'active' || !$slot->scheduled_payout_date) continue;

                $due = $slot->scheduled_payout_date->copy()->addDays($grace);
                if ($today->lte($due)) continue;

                // Already past grace
                $action = $project->missed_payment_action ?? 'mark';

                // Past grace period
                if ($action === 'fine' && ($project->late_fee ?? 0) > 0) {
                    $this->info("Fine ₦{$project->late_fee} on slot {$slot->id} (user {$slot->user_id})");
                    if ($slot->user && class_exists($notificationClass)) {
                        $slot->user->notify(new $notificationClass($slot, $project));
                    }
                } elseif ($action === 'remove') {
                    $slot->update(['status' => 'open', 'user_id' => null]);
                    $this->info("Removed user {$slot->user_id} from slot {$slot->id}");
                    if ($slot->user && class_exists($notificationClass)) {
                        $slot->user->notify(new $notificationClass($slot, $project));
                    }
                } else {
                    // mark
                    $slot->update(['is_late' => true]); // if you added the column
                    $this->info("Marked late: slot {$slot->id}");
                    if ($slot->user && class_exists($notificationClass)) {
                        $slot->user->notify(new $notificationClass($slot, $project));
                    }
                }
            }
        }

        // Release reserved slots older than 24 hours
        \App\Models\ProjectPayoutSlot::where('status', 'reserved')
            ->whereNotNull('reserved_at')
            ->where('reserved_at', '<', now()->subHours(24))
            ->update([
                'status' => 'open',
                'user_id' => null,
                'reserved_at' => null,
            ]);

        $this->info('Circle late check complete.');
        return 0;
    }
}