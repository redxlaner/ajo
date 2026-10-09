<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WelfareRequest extends Model
{
    protected $fillable = [
        'user_id',
        'request_type',
        'amount_requested',
        'description',
        'supporting_docs',
        'status',
        'reviewed_by',
        'review_notes',
        'decision_date',
        'approved_amount',
        'disbursed_by',
        'disbursed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount_requested' => 'decimal:2',
        'decision_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function disbursedBy()
{
    return $this->belongsTo(User::class, 'disbursed_by');
}
}