<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamPollOption extends Model
{
    protected $fillable = ['team_poll_id', 'label'];

    public function votes()
    {
        return $this->hasMany(TeamPollVote::class);
    }
}