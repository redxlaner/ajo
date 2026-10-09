<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectContribution extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'amount', 'proof_of_payment',
        'status', 'confirmed_by', 'confirmed_at', 'proof_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}