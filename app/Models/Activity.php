<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'user_id', 'community_id', 'project_id', 'parent_id',
        'type', 'title', 'body', 'quote_body', 'url', 'meta','attachment',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /** Record an activity */
    public static function log(array $data): self
    {
        return static::create(array_merge($data, [
            'attachment' => $data['attachment'] ?? null,
        ]));
    }

    public function likes()
    {
        return $this->hasMany(ActivityLike::class);
    }

    public function comments()
    {
        return $this->hasMany(ActivityComment::class)->latest();
    }

    public function parent()
    {
        return $this->belongsTo(Activity::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Activity::class, 'parent_id');
    }

    public function isLikedBy($user): bool
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function bookmarks()
    {
        return $this->hasMany(ActivityBookmark::class);
    }

    public function isBookmarkedBy(?\App\Models\User $user): bool
    {
        if (!$user) return false;
        return $this->bookmarks()->where('user_id', $user->id)->exists();
    }

    public function reposts()
    {
        return $this->hasMany(self::class, 'parent_id')->where('type', 'repost');
    }

    public function quotes()
    {
        return $this->hasMany(self::class, 'parent_id')->where('type', 'quote');
    }

    public function isRepostedBy($user): bool
    {
        if (!$user) return false;

        return static::where('user_id', $user->id)
            ->where('parent_id', $this->id)
            ->where('type', 'repost')
            ->exists();
    }
}