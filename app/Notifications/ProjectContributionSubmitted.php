<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectContributionSubmitted extends Notification
{
    use Queueable;
public function __construct(public $contribution) {}

public function via($notifiable): array
{
    return ['mail', 'database'];
}

public function toMail($notifiable): MailMessage
{
    $p = $this->contribution->project;

    return (new MailMessage)
        ->subject('New contribution · ' . $p->title)
        ->greeting('Hello ' . $notifiable->name . ',')
        ->line($this->contribution->user->name . ' submitted ₦' . number_format($this->contribution->amount, 2))
        ->line('Project: **' . $p->title . '**')
        ->action('Review', url('/teams/' . $p->community->slug . '/projects/' . $p->id));
}

public function toArray($notifiable): array
{
    $p = $this->contribution->project;
    return [
        'message' => $this->contribution->user->name . ' contributed ₦' . number_format($this->contribution->amount, 0) . ' to ' . $p->title,
        'url' => route('projects.show', [$p->community->slug, $p->id]),
    ];
}
}
