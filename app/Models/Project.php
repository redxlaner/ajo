<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'community_id',
        'creator_id',
        'title',
        'description',
        'goal_amount',
        'deadline',
        'cover_image',
        'status',
        'contribution_type',
        'cadence',
        'recurring_amount',
        'project_type',
        'member_target',
        'visibility',
        'frequency',
        'contribution_amount',
        'slots_total',
        'start_date',
        'bank_name',
        'account_name',
        'account_number',
        'summary',
        'legacy_circle_id',
        'grace_days',
        'late_fee',
        'missed_payment_action',
            ];

    
        protected $casts = [
            'start_date' => 'date',
            'contribution_amount' => 'decimal:2',
            'goal_amount' => 'decimal:2',
            'deadline' => 'date',
        ];
    

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function vault()
    {
        return $this->hasOne(ProjectVault::class);
    }

    public function contributions()
    {
        return $this->hasMany(ProjectContribution::class);
    }

    public function confirmedContributions()
    {
        return $this->contributions()->where('status', 'confirmed');
    }

    public function progressPercent(): float
    {
        if ($this->goal_amount <= 0) return 0;
        $raised = $this->vault?->balance ?? 0;
        return min(100, round(($raised / $this->goal_amount) * 100, 1));
    }

    public function donors()
    {
        return $this->confirmedContributions()
            ->select('user_id')
            ->distinct()
            ->with('user');
    }

    public function vaultActions()
    {
        return $this->hasMany(VaultAction::class);
    }

    public function ballot()
    {
        return $this->hasOne(\App\Models\ProjectBallot::class)->latestOfMany();
    }

    public function payoutSlots()
    {
        return $this->hasMany(\App\Models\ProjectPayoutSlot::class)->orderBy('position');
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isRotational(): bool
    {
        return $this->project_type === 'rotational';
    }

    public function isGoal(): bool
    {
        return $this->project_type === 'goal';
    }
}