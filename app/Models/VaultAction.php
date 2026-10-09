<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaultAction extends Model
{
    protected $fillable = [
        'project_id', 'requested_by', 'type', 'amount',
        'note', 'status', 'resolved_at', 'fee_amount', 'net_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function votes()
    {
        return $this->hasMany(VaultActionVote::class);
    }

    public function donorIds()
    {
        return $this->project->confirmedContributions()
            ->pluck('user_id')
            ->unique()
            ->values();
    }

    public function allDonorsApproved(): bool
    {
        $donorIds = $this->donorIds();
        if ($donorIds->isEmpty()) {
            return false;
        }

        $approvals = $this->votes()
            ->where('vote', 'approve')
            ->whereIn('user_id', $donorIds)
            ->pluck('user_id')
            ->unique();

        return $donorIds->diff($approvals)->isEmpty();
    }

    public function hasRejection(): bool
    {
        return $this->votes()->where('vote', 'reject')->exists();
    }
}