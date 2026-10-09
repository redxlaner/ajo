<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentCycle extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'total_profit',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_profit' => 'decimal:2',
    ];
}