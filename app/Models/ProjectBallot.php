<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectBallot extends Model
{
    protected $fillable = ['project_id', 'executed_by', 'draw_hash', 'executed_at', 'swap_until', 'status'];
    protected $casts = ['executed_at' => 'datetime', 'swap_until' => 'datetime'];

    public function slots()
    {
        return $this->hasMany(ProjectPayoutSlot::class, 'ballot_id');
    }

    public function swapsOpen(): bool
    {
        return $this->status === 'pending_swap' && now()->lt($this->swap_until);
    }
}