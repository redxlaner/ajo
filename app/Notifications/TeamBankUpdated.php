<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamBankUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
    public $community,
    public string $mode // 'team' | 'platform'
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $line = $this->mode === 'team'
            ? 'This team now uses a **team bank account** for project contributions.'
            : 'This team now uses **Ajo platform escrow** for project contributions.';

        return (new MailMessage)
            ->subject('Bank settings · ' . $this->community->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($line)
            ->line('Team: **' . $this->community->name . '**')
            ->action('Open team', url('/teams/' . $this->community->slug));
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->mode === 'team'
                ? $this->community->name . ' now uses a team bank account'
                : $this->community->name . ' now uses Ajo escrow',
            'url' => route('teams.show', $this->community->slug),
        ];
    }
}
