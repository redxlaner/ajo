<?php

namespace App\Console\Commands;

use App\Models\Community;
use App\Models\Project;
use App\Models\ProjectPayoutSlot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateCirclesToProjects extends Command
{
    protected $signature = 'circles:migrate-to-projects';
    protected $description = 'Move existing circles into rotational projects under a host-owned team';

    public function handle(): int
    {
        if (!class_exists(\App\Models\Circle::class)) {
            $this->error('Circle model not found. Nothing to migrate.');
            return self::FAILURE;
        }

        $circles = \App\Models\Circle::with(['slots', 'host'])->get();

        if ($circles->isEmpty()) {
            $this->info('No circles to migrate.');
            return self::SUCCESS;
        }

        $this->info("Migrating {$circles->count()} circles...");

        foreach ($circles as $circle) {
            DB::transaction(function () use ($circle) {
                // 1. Find or create host team
                $teamName = $circle->host->name . ' Circles';
                $slug = Str::slug($teamName . '-' . $circle->host_id);

                $team = Community::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $teamName,
                        'description' => 'Auto-created team for migrated circles.',
                        'created_by' => $circle->host_id,
                        'visibility' => 'public',
                        'invites_open' => true,
                    ]
                );

                // Ensure host is admin member
                $team->members()->firstOrCreate(
                    ['user_id' => $circle->host_id],
                    ['role' => 'admin', 'status' => 'active']
                );

                // 2. Create rotational project
                $project = Project::create([
                    'community_id' => $team->id,
                    'creator_id' => $circle->host_id,
                    'title' => $circle->title,
                    'description' => $circle->summary,
                    'summary' => $circle->summary,
                    'project_type' => 'rotational',
                    'visibility' => 'public',
                    'status' => 'active',
                    'frequency' => $circle->frequency,
                    'contribution_amount' => $circle->contribution_amount,
                    'slots_total' => $circle->slots_total,
                    'start_date' => $circle->start_date,
                    'bank_name' => $circle->bank_name,
                    'account_name' => $circle->account_name,
                    'account_number' => $circle->account_number,
                    'legacy_circle_id' => $circle->id,
                    'goal_amount' => 0,
                ]);

                // 3. Create slots
                foreach ($circle->slots as $slot) {
                    ProjectPayoutSlot::create([
                        'project_id' => $project->id,
                        'position' => $slot->position,
                        'user_id' => $slot->user_id,
                        'status' => $slot->status ?? 'open',
                        'eligibility' => $slot->eligibility ?? 'new',
                        'scheduled_payout_date' => $slot->payout_due_on,
                        'reserved_until' => $slot->reserved_until,
                        'paid_out_at' => $slot->paid_out_at,
                    ]);
                }

                $this->line("  → {$circle->title} → project #{$project->id} in team {$team->slug}");
            });
        }

        $this->info('Migration complete.');
        return self::SUCCESS;
    }
}