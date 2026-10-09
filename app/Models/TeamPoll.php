<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamPoll extends Model
{
    protected $fillable = ['community_id', 'created_by', 'question', 'closes_at', 'status'];
    protected $casts = ['closes_at' => 'datetime'];

    public function options()
    {
        return $this->hasMany(TeamPollOption::class);
    }

    public function votes()
    {
        return $this->hasMany(TeamPollVote::class);
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }
        return !$this->closes_at || $this->closes_at->isFuture();
    }

    public function community()
    {
        return $this->belongsTo(\App\Models\Community::class);
    }
}