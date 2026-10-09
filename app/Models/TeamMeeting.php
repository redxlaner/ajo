<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMeeting extends Model
{
    protected $fillable = [
        'community_id', 'created_by', 'title', 'agenda',
        'meet_link', 'location', 'starts_at', 'ends_at',
        'status', 'reminded_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'reminded_at' => 'datetime',
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}