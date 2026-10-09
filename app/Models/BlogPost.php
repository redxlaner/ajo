<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'category', 'excerpt', 'body', 'cover', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public static function categories(): array
    {
        return [
            'money-tips' => 'Money tips',
            'investing' => 'Investing',
            'wealth-teams' => 'Wealth teams',
            'starting-up' => 'Starting up',
            'goal-discovery' => 'Goal discovery',
            'personal-development' => 'Personal development',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if (!$post->slug) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function likes()
    {
        return $this->hasMany(\App\Models\BlogLike::class);
    }

    public function likedBy(?\App\Models\User $user): bool
    {
        return $user && $this->likes()->where('user_id', $user->id)->exists();
    }
}