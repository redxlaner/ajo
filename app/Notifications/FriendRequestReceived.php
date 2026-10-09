<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FriendRequestReceived extends Notification
{
    use Queueable;

    public function __construct(public User $from) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->from->name.' sent you a friend request on Ajo')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->from->name.' wants to connect with you on Ajo.')
            ->action('View requests', route('friends.index'))
            ->line('You can accept or decline from your Friends page.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->from->name.' sent you a friend request',
            'url' => route('friends.index'),
        ];
    }
}