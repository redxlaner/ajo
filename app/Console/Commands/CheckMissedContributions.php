<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\MonthlyContribution;
use Carbon\Carbon;

class CheckMissedContributions extends Command
{
    protected $signature = 'contributions:check-missed';
    protected $description = 'Check for members who missed contributions in the previous month and update suspension status';

    public function handle()
    {
        // We check the previous month
        $previousMonth = Carbon::now()->subMonth()->format('Y-m') . '-01';
        $currentYear = Carbon::now()->year;

        $this->info("Checking missed contributions for: " . Carbon::parse($previousMonth)->format('F Y'));

        $members = User::role('member')->get(); // only regular members
        // If you want to include officers too, remove the role filter:
        // $members = User::all();

        $missedCount = 0;
        $suspendedCount = 0;

        foreach ($members as $member) {
            // Skip already suspended users
            if ($member->is_suspended) {
                continue;
            }

            // Check if they have an approved contribution for the previous month
            $hasContribution = MonthlyContribution::where('user_id', $member->id)
                ->where('month', $previousMonth)
                ->where('status', 'approved')
                ->exists();

            if (!$hasContribution) {
                // Reset counter if year changed
                if ($member->suspension_year != $currentYear) {
                    $member->missed_contributions_count = 0;
                    $member->suspension_year = $currentYear;
                }

                $member->missed_contributions_count += 1;
                $missedCount++;

                if ($member->missed_contributions_count >= 3) {
                    $member->is_suspended = true;
                    $suspendedCount++;
                    $this->warn("Suspended: {$member->name}");
                }

                $member->save();
                $this->line("Missed: {$member->name} (Total misses this year: {$member->missed_contributions_count})");
            }
        }

        $this->info("Done. {$missedCount} members missed contribution. {$suspendedCount} members suspended.");
        return Command::SUCCESS;
    }
}