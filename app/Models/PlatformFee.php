<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFee extends Model
{
    protected $fillable = [
        'vault_action_id', 'project_id', 'community_id',
        'gross_amount', 'fee_rate', 'fee_amount', 'net_amount', 'status',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'fee_rate' => 'decimal:4',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function action()
    {
        return $this->belongsTo(VaultAction::class, 'vault_action_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}