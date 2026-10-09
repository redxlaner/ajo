<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectContributionResolved extends Notification
{
    use Queueable;

    public function __construct(public $contribution) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $ok = $this->contribution->status === 'confirmed';
        $p = $this->contribution->project;

        return (new MailMessage)
            ->subject(($ok ? 'Contribution confirmed' : 'Contribution rejected') . ' · ' . $p->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your contribution of ₦' . number_format($this->contribution->amount, 2) . ' was **' . $this->contribution->status . '**.')
            ->action('View project', url('/teams/' . $p->community->slug . '/projects/' . $p->id));
    }

    public function toArray($notifiable): array
    {
        $p = $this->contribution->project;
        return [
            'message' => 'Your contribution to ' . $p->title . ' was ' . $this->contribution->status,
            'url' => route('projects.show', [$p->community->slug, $p->id]),
        ];
    }
}
