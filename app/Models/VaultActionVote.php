<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaultActionVote extends Model
{
    protected $fillable = [
        'vault_action_id', 'user_id', 'vote',
    ];

    public function action()
    {
        return $this->belongsTo(VaultAction::class, 'vault_action_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}