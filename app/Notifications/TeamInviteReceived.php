<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInviteReceived extends Notification
{
    use Queueable;

    public function __construct(public $community, public $inviter) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invitation · ' . $this->community->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->inviter->name . ' invited you to join **' . $this->community->name . '**.')
            ->action('View team', url('/teams/' . $this->community->slug))
            ->line('Open the team page to accept or request membership.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->inviter->name . ' invited you to ' . $this->community->name,
            'url' => route('teams.show', $this->community->slug),
        ];
    }
}
