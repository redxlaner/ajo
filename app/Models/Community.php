<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Community extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'cover_image',
        'created_by', 'is_discoverable', 'members_count',
        'bank_name', 'account_number', 'account_name', 
        'visibility', 'member_limit', 'invites_open',
    ];

    protected $casts = [
        'is_discoverable' => 'boolean',
        'member_limit' => 'integer',
        'invites_open' => 'boolean',
        'visibility' => 'string'
    ];

    protected static function booted()
    {
        static::creating(function ($community) {
            if (empty($community->slug)) {
                $community->slug = Str::slug($community->name) . '-' . Str::random(6);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function activeMembers()
    {
        return $this->members()->where('status', 'active');
    }

    public function pendingMembers()
    {
        return $this->members()->where('status', 'pending');
    }

    public function isMember(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function isAdmin(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('role', 'admin')
            ->exists();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function posts()
    {
        return $this->hasMany(CommunityPost::class)->latest();
    }

    public function bankDetails(): array
    {
        if ($this->account_number) {
            return [
                'source' => 'team',
                'label' => $this->name . ' account',
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'account_name' => $this->account_name,
            ];
        }

        return [
            'source' => 'platform',
            'label' => 'Ajo escrow (locked vault)',
            'bank_name' => \App\Models\Setting::get('bank_name', 'N/A'),
            'account_number' => \App\Models\Setting::get('account_number', 'N/A'),
            'account_name' => \App\Models\Setting::get('account_name', 'N/A'),
        ];
    }

    public function meetings()
    {
        return $this->hasMany(\App\Models\TeamMeeting::class);
    }

    public function polls()
    {
        return $this->hasMany(\App\Models\TeamPoll::class);
    }

    public function isPrivate(): bool
{
    return $this->visibility === 'private';
}

public function activeMemberCount(): int
{
    return $this->members()->where('status', 'active')->count();
}

public function slotsLeft(): ?int
{
    if (!$this->member_limit) {
        return null;
    }

    return max(0, (int) $this->member_limit - $this->activeMemberCount());
}

public function isFull(): bool
{
    return $this->member_limit && $this->slotsLeft() === 0;
}

public function invitesOpen(): bool
{
    return (bool) $this->invites_open && !$this->isFull();
}
}