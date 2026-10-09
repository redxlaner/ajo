<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PledgeDue extends Notification
{
    use Queueable;

    public function __construct(public $pledge, public bool $late = false) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->pledge->project;
        $team = $project->community;
        $line = $this->late
            ? 'Your ₦' . number_format($this->pledge->amount, 0) . ' contribution to ' . $project->title . ' is late.'
            : 'Your ₦' . number_format($this->pledge->amount, 0) . ' contribution to ' . $project->title . ' is due.';

        return (new MailMessage)
            ->subject($this->late ? 'Contribution late' : 'Contribution due')
            ->greeting('Hello ' . $notifiable->name)
            ->line($line)
            ->action('Open project', route('projects.show', [$team->slug, $project->id]));
    }

    public function toArray(object $notifiable): array
    {
        $project = $this->pledge->project;

        return [
            'message' => ($this->late ? 'Late: ' : 'Due: ') . '₦' . number_format($this->pledge->amount, 0) . ' for ' . $project->title,
            'url' => route('projects.show', [$project->community->slug, $project->id]),
        ];
    }
}