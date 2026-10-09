<?php

namespace App\Notifications;

use App\Models\Community;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamJoinApproved extends Notification
{
    use Queueable;

    public function __construct(public Community $team) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You’re in · ' . $this->team->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your request to join ' . $this->team->name . ' was approved.')
            ->action('Open team feed', route('teams.feed', $this->team->slug));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'You were approved to join ' . $this->team->name,
            'url' => route('teams.feed', $this->team->slug),
        ];
    }
}