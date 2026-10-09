<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JoinRequestResolved extends Notification
{
    use Queueable;

    
   public function __construct(public $community, public string $status) {} // approved|rejected

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $approved = $this->status === 'approved';

        return (new MailMessage)
            ->subject(($approved ? 'Approved' : 'Rejected') . ' · ' . $this->community->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($approved
                ? 'Your request to join **' . $this->community->name . '** was approved.'
                : 'Your request to join **' . $this->community->name . '** was rejected.')
            ->action($approved ? 'Open team' : 'Discover teams', $approved
                ? url('/teams/' . $this->community->slug)
                : url('/teams'));
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->status === 'approved'
                ? 'You were approved to join ' . $this->community->name
                : 'Your request to join ' . $this->community->name . ' was rejected',
            'url' => $this->status === 'approved'
                ? route('teams.show', $this->community->slug)
                : route('teams.index'),
        ];
    }
}
