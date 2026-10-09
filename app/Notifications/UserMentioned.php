<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class UserMentioned extends Notification
{
    public function __construct(public $activity, public $actor, public string $context = 'post') {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->actor->name . ' mentioned you',
            'url' => $this->activity->url ?? url('/dashboard'),
        ];
    }
}