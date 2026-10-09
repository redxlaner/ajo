<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ActivityQuoted extends Notification
{
    public function __construct(public $activity, public $actor) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->actor->name . ' quoted your post',
            'url' => $this->activity->url ?? url('/dashboard'),
        ];
    }
}