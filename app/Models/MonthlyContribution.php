<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyContribution extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'total_amount',
    'welfare_amount',
    'investment_amount',
    'operations_amount',
    'month',
    'status',
    'proof_of_payment',
    'approved_by',
    'approved_at',
    'rejection_reason',
    'penalty_amount',
    'is_late',
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