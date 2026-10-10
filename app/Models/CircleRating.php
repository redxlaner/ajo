<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CircleRating extends Model
{
    protected $fillable = ['circle_id', 'host_id', 'user_id', 'score', 'comment'];
}