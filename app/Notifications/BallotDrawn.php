<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BallotDrawn extends Notification
{
    use Queueable;

    public function __construct(public $project, public int $position, public $team) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Ajo slot is #'.$this->position)
            ->line('The payout order for '.$this->project->title.' has been drawn.')
            ->line('Your slot is #'.$this->position.'.')
            ->action('View roster', route('projects.show', [$this->team->slug, $this->project->id]));
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Your slot in '.$this->project->title.' is #'.$this->position.'.',
            'url' => route('projects.show', [$this->team->slug, $this->project->id]),
        ];
    }
}