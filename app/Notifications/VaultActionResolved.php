<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VaultActionResolved extends Notification
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

        return (new MailMessage)
            ->subject('Vault update · ' . $p->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The **' . $this->action->type . '** request is now **' . $this->action->status . '**.')
            ->action('View project', url('/teams/' . $p->community->slug . '/projects/' . $p->id));
    }

    public function toArray($notifiable): array
    {
        $p = $this->action->project;
        return [
            'message' => ucfirst($this->action->type) . ' for ' . $p->title . ' was ' . $this->action->status,
            'url' => route('projects.show', [$p->community->slug, $p->id]),
        ];
    }
}
