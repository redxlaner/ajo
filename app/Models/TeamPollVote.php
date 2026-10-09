<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamPollVote extends Model
{
    protected $fillable = ['team_poll_id', 'team_poll_option_id', 'user_id'];
}