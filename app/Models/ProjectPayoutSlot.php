<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPayoutSlot extends Model
{
    protected $fillable = ['project_id', 'ballot_id', 'user_id', 'position', 'original_position', 'scheduled_payout_date', 'status',
'reserved_until',
'paid_out_at',
'eligibility',];
    protected $casts = [
    'scheduled_payout_date' => 'date',
    'reserved_until' => 'datetime',
    'paid_out_at' => 'datetime',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}