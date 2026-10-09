<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VaultActionRequested extends Notification
{
    use Queueable;

    public function __construct(public $action) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $p = $this->action->project;
        $label = $this->action->type === 'claim' ? 'claim funds' : 'terminate the project';

        return (new MailMessage)
            ->subject('Your approval needed · ' . $p->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The creator requested to **' . $label . '** for **' . $p->title . '**.')
            ->line('Amount: ₦' . number_format($this->action->amount, 2))
            ->line('As a donor, your vote is required.')
            ->action('Review & vote', url('/teams/' . $p->community->slug . '/projects/' . $p->id));
    }

    public function toArray($notifiable): array
    {
        $p = $this->action->project;
        return [
            'message' => 'Approve or reject ' . $this->action->type . ' for ' . $p->title,
            'url' => route('projects.show', [$p->community->slug, $p->id]),
        ];
    }
}
