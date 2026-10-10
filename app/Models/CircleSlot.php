<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CircleSlot extends Model
{
    protected $fillable = [
        'circle_id', 'position', 'user_id', 'status', 'reserved_until', 'payout_due_on', 'eligibility',
        'paid_out_at', 'paid_out_by',
    ];

    protected $casts = [
        'reserved_until' => 'datetime',
        'payout_due_on' => 'date',
    ];

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CirclePayment::class);
    }
}