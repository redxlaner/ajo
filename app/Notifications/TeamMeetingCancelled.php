<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMeetingCancelled extends Notification
{
    use Queueable;

    public function __construct(public $meeting, public $actor) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Meeting cancelled: ' . $this->meeting->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line($this->actor->name . ' cancelled "' . $this->meeting->title . '".')
            ->line('It was set for ' . $this->meeting->starts_at->format('D d M, g:i A') . '.')
            ->action('Open team', route('teams.meetings.index', $this->meeting->community->slug));
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->actor->name . ' cancelled "' . $this->meeting->title . '".',
            'url' => route('teams.meetings.index', $this->meeting->community->slug),
        ];
    }
}