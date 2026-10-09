<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JoinRequestReceived extends Notification
{
    use Queueable;

    public function __construct(public $community, public $requester) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Join request · ' . $this->community->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->requester->name . ' requested to join **' . $this->community->name . '**.')
            ->action('Review request', url('/teams/' . $this->community->slug))
            ->line('Approve or reject from the team page.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->requester->name . ' requested to join ' . $this->community->name,
            'url' => route('teams.show', $this->community->slug),
        ];
    }
}
