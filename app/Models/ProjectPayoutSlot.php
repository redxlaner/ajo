<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPayoutSlot extends Model
{
    protected $fillable = ['project_id', 'ballot_id', 'user_id', 'position', 'original_position', 'scheduled_payout_date', 'status'];
    protected $casts = ['scheduled_payout_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}