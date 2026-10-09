<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentCommitment extends Model
{
    protected $fillable = [
        'offering_id', 'user_id', 'units_pledged',
        'status', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'units_pledged' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function offering()
    {
        return $this->belongsTo(InvestmentOffering::class, 'offering_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}