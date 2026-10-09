<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FriendRequestAccepted extends Notification
{
    use Queueable;

    public function __construct(public User $accepter) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->accepter->name . ' accepted your friend request')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->accepter->name . ' is now your friend on Ajo.')
            ->action('View profile', route('users.show', $this->accepter));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->accepter->name . ' accepted your friend request',
            'url' => route('users.show', $this->accepter),
        ];
    }
}