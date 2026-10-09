<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentHolding extends Model
{
    protected $fillable = [
        'offering_id', 'user_id', 'units', 'average_cost',
    ];

    protected $casts = [
        'units' => 'decimal:2',
        'average_cost' => 'decimal:2',
    ];

    public function offering()
    {
        return $this->belongsTo(InvestmentOffering::class, 'offering_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}