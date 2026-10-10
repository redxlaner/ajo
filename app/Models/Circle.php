<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Circle extends Model
{
    protected $fillable = [
        'community_id', 'host_id', 'title', 'slug', 'summary',
        'contribution_amount', 'frequency', 'slots_total', 'start_date',
        'status', 'visibility', 'kyc_level', 'collateral_required', 'payout_order',
        'bank_name', 'account_name', 'account_number',
    ];

    protected $casts = [
        'start_date' => 'date',
        'collateral_required' => 'boolean',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(CircleSlot::class)->orderBy('position');
    }

    public function openSlots(): HasMany
    {
        return $this->slots()->where('status', 'open');
    }

    public function messages()
    {
        return $this->hasMany(CircleMessage::class);
    }

    public function ratings()
    {
        return $this->hasMany(\App\Models\CircleRating::class);
    }
}