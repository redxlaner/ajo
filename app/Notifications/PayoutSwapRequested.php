<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutSwapRequested extends Notification
{
    use Queueable;

    public function __construct(public $project, public $slot, public $from) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->from->name.' wants to swap Ajo slots')
            ->line($this->from->name.' asked to trade payout slots in '.$this->project->title.'.')
            ->action('Review swap', route('projects.show', [$this->project->community->slug ?? $this->slot->project->community->slug, $this->project->id]));
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->from->name.' wants to swap slots in '.$this->project->title.'.',
            'url' => route('projects.show', [$this->project->community->slug, $this->project->id]),
        ];
    }
}