<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CircleMessage extends Model
{
    protected $fillable = ['circle_id', 'from_user_id', 'to_user_id', 'body', 'read_at'];

    public function from()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}