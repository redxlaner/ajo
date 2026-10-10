<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CirclePayment extends Model
{
    protected $fillable = [
        'circle_slot_id', 'user_id', 'amount', 'paid_on', 'proof_path',
        'status', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'paid_on' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(CircleSlot::class, 'circle_slot_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}