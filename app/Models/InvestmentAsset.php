<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentAsset extends Model
{
    protected $fillable = [
        'name', 'type', 'purchase_value', 'current_value',
        'purchase_date', 'maturity_date', 'asset_manager',
        'status', 'description', 'added_by'
    ];

    protected $casts = [
        'purchase_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'purchase_date' => 'date',
        'maturity_date' => 'date',
    ];

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}