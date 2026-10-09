<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_units',
        'total_invested',
        'average_price'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}