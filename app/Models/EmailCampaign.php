<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    protected $fillable = ['user_id', 'subject', 'body', 'audience', 'sent_count', 'status', 'sent_at'];
    protected $casts = ['sent_at' => 'datetime'];
}