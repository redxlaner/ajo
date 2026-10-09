<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectVault extends Model
{
    protected $fillable = [
        'project_id', 'balance', 'status', 'opened_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'opened_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }
}