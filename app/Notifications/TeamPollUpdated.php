<?php

namespace App\Notifications;

use App\Models\TeamPoll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamPollUpdated extends Notification
{
    use Queueable;

    public function __construct(public TeamPoll $poll, public string $event = 'opened') {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $team = $this->poll->community->name ?? 'your team';
        $line = $this->event === 'closed'
            ? 'A poll in ' . $team . ' has been closed.'
            : 'A new poll is open in ' . $team . '.';

        return (new MailMessage)
            ->subject(($this->event === 'closed' ? 'Poll closed: ' : 'New poll: ') . $this->poll->question)
            ->greeting('Hello ' . $notifiable->name)
            ->line($line)
            ->line($this->poll->question)
            ->action('Open team feed', route('teams.feed', $this->poll->community->slug));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => ($this->event === 'closed' ? 'Poll closed: ' : 'New poll: ') . $this->poll->question,
            'url' => route('teams.feed', $this->poll->community->slug),
        ];
    }
}