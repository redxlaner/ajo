<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentOffering extends Model
{
    protected $fillable = [
        'name', 'type', 'description',
        'target_capital', 'target_units', 'min_units',
        'unit_price_at_open', 'start_date', 'maturity_date',
        'status', 'created_by',
        'images',
    ];

    protected $casts = [
        'target_capital' => 'decimal:2',
        'target_units' => 'decimal:2',
        'min_units' => 'decimal:2',
        'unit_price_at_open' => 'decimal:2',
        'start_date' => 'date',
        'maturity_date' => 'date',
        'images' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function commitments()
    {
        return $this->hasMany(InvestmentCommitment::class, 'offering_id');
    }

    public function approvedUnits()
    {
        return $this->commitments()->where('status', 'approved')->sum('units_pledged');
    }

    public function progressPercent()
    {
        if ($this->target_units <= 0) return 0;
        return min(100, round(($this->approvedUnits() / $this->target_units) * 100, 1));
    }
}