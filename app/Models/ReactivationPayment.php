<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReactivationPayment extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'proof_of_payment',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}