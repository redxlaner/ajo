<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPledge extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'amount', 'cadence',
        'next_due_on', 'status', 'due_reminded_at', 'late_reminded_at',
    ];

    protected $casts = [
        'next_due_on' => 'date',
        'due_reminded_at' => 'datetime',
        'late_reminded_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}