<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DirectMessageReceived extends Notification
{
    use Queueable;

    public function __construct(
        public User $sender,
        public Conversation $conversation
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->sender->name . ' sent you a message',
            'url' => route('messages.show', $this->sender),
        ];
    }
}