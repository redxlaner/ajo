<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Friendship;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rank',
        'service_number',
        'phone',
        'avatar',
        'occupation',
        'marital_status',
        'date_of_birth',
        'gender',
        'city',
        'state',
        'bio',
        'is_suspended',
        'missed_contributions_count',
        'suspension_year',
        'bank_name',
        'account_number',
        'account_name',
        'invite_code',
        'invited_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_suspended' => 'boolean',
            'phone_verified_at' => 'datetime',
            'phone_otp_expires_at' => 'datetime',
        ];
    }

    public function avatarUrl(): string
    {
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . ltrim($this->avatar, '/'));
        }

        $name = urlencode($this->name ?: 'U');

        return "https://ui-avatars.com/api/?name={$name}&background=4f46e5&color=fff&size=128&bold=true";
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name ?: 'U'));
        $letters = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));

        return $letters !== '' ? $letters : 'U';
    }

    public function hasAvatar(): bool
    {
        return $this->avatar
            && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar);
    }

    public function monthlyContributions()
    {
        return $this->hasMany(MonthlyContribution::class);
    }

    public function investmentUnit()
    {
        return $this->hasOne(InvestmentUnit::class);
    }

    public function welfareRequests()
    {
        return $this->hasMany(WelfareRequest::class);
    }

    public function isSuspended(): bool
    {
        return (bool) $this->is_suspended;
    }

    public function recordMissedContribution(): void
    {
        $currentYear = now()->year;

        if ($this->suspension_year !== $currentYear) {
            $this->missed_contributions_count = 0;
            $this->suspension_year = $currentYear;
        }

        $this->missed_contributions_count += 1;

        if ($this->missed_contributions_count >= 3) {
            $this->is_suspended = true;
        }

        $this->save();
    }

    public function unitPurchases()
    {
        return $this->hasMany(\App\Models\UnitPurchase::class);
    }

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'community_members')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function communityMemberships()
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function sentFriendRequests()
{
    return $this->hasMany(\App\Models\Friendship::class, 'user_id');
}

public function receivedFriendRequests()
{
    return $this->hasMany(\App\Models\Friendship::class, 'friend_id');
}

/** Accepted friend user IDs (either direction). */
public function friendIds(): \Illuminate\Support\Collection
{
    $sent = \App\Models\Friendship::where('user_id', $this->id)
        ->where('status', 'accepted')->pluck('friend_id');
    $received = \App\Models\Friendship::where('friend_id', $this->id)
        ->where('status', 'accepted')->pluck('user_id');
    return $sent->merge($received)->unique()->values();
}

public function friendsCount(): int
{
    return $this->friendIds()->count();
}

public function isFriendsWith(\App\Models\User $other): bool
{
    return \App\Models\Friendship::where('status', 'accepted')
        ->where(function ($q) use ($other) {
            $q->where('user_id', $this->id)->where('friend_id', $other->id)
              ->orWhere(function ($q2) use ($other) {
                  $q2->where('user_id', $other->id)->where('friend_id', $this->id);
              });
        })
        ->exists();
}

public function pendingFriendRequestTo(\App\Models\User $other): ?\App\Models\Friendship
{
    return \App\Models\Friendship::where('user_id', $this->id)
        ->where('friend_id', $other->id)
        ->where('status', 'pending')
        ->first();
}

public function pendingFriendRequestFrom(\App\Models\User $other): ?\App\Models\Friendship
{
    return \App\Models\Friendship::where('user_id', $other->id)
        ->where('friend_id', $this->id)
        ->where('status', 'pending')
        ->first();
}

public function suggestedFriends(int $limit = 10): \Illuminate\Support\Collection
{
    $me = $this->id;
    $already = $this->friendIds()->push($me)->unique();

    // Pending either direction — don't suggest again
    $pending = \App\Models\Friendship::where('status', 'pending')
        ->where(function ($q) use ($me) {
            $q->where('user_id', $me)->orWhere('friend_id', $me);
        })
        ->get()
        ->flatMap(fn ($f) => [$f->user_id, $f->friend_id])
        ->unique();

    $exclude = $already->merge($pending)->unique();

    // Teammates
    $myTeamIds = $this->communityMemberships()
        ->where('status', 'active')
        ->pluck('community_id');

    $teammateIds = \App\Models\CommunityMember::whereIn('community_id', $myTeamIds)
        ->where('status', 'active')
        ->whereNotIn('user_id', $exclude)
        ->pluck('user_id');

    // Friends of friends
    $fofIds = collect();
    foreach ($this->friendIds() as $fid) {
        $fofIds = $fofIds->merge(
            \App\Models\User::find($fid)?->friendIds() ?? []
        );
    }
    $fofIds = $fofIds->unique()->diff($exclude);

    // Score: teammate +2, mutual friend +1 each
    $scores = [];
    foreach ($teammateIds as $id) {
        $scores[$id] = ($scores[$id] ?? 0) + 2;
    }
    foreach ($fofIds as $id) {
        $scores[$id] = ($scores[$id] ?? 0) + 1;
    }

    arsort($scores);
    $topIds = collect(array_keys($scores))->take($limit);

    if ($topIds->isEmpty()) {
        return collect();
    }

    $users = \App\Models\User::whereIn('id', $topIds)->get()->keyBy('id');

    return $topIds->map(function ($id) use ($users, $teammateIds) {
        $u = $users->get($id);
        if (!$u) {
            return null;
        }
        $mutual = $this->friendIds()->intersect($u->friendIds())->count();
        $sharedTeams = $teammateIds->contains($id);
        return (object) [
            'user' => $u,
            'mutual' => $mutual,
            'from_team' => $sharedTeams,
        ];
    })->filter()->values();
}

public function findForLogin(string $login): ?self
{
    $login = trim($login);

    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        return static::where('email', $login)->first();
    }

    // Normalize: keep digits (and leading + if you store international)
    $phone = preg_replace('/\s+/', '', $login);

    return static::where('phone', $phone)
        ->orWhere('phone', ltrim($phone, '+'))
        ->first();
}

public function isFriendWith(User $other): bool
{
    return $this->isFriendsWith($other);
}

public function isKnownTo(\App\Models\User $host): bool
{
    if ($this->id === $host->id) {
        return true;
    }

    $friends = \App\Models\Friendship::where('status', 'accepted')
        ->where(function ($q) use ($host) {
            $q->where('user_id', $this->id)->where('friend_id', $host->id);
        })->orWhere(function ($q) use ($host) {
            $q->where('user_id', $host->id)->where('friend_id', $this->id)->where('status', 'accepted');
        })->exists();

    if ($friends) {
        return true;
    }

    $hostTeamIds = \App\Models\CommunityMember::where('user_id', $host->id)->where('status', 'active')->pluck('community_id');
    $sharesTeam = \App\Models\CommunityMember::where('user_id', $this->id)
        ->where('status', 'active')
        ->whereIn('community_id', $hostTeamIds)
        ->exists();

    if ($sharesTeam) {
        return true;
    }

    return \App\Models\CirclePayment::where('user_id', $this->id)->where('status', 'approved')->exists();
}
}