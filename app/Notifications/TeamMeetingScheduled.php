<?php

namespace App\Notifications;

use App\Models\TeamMeeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMeetingScheduled extends Notification
{
    use Queueable;

    public function __construct(public TeamMeeting $meeting, public bool $reminder = false) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $when = $this->meeting->starts_at->format('D d M Y, g:i A');
        $team = $this->meeting->community->name ?? 'your team';
        $subject = $this->reminder
            ? 'Reminder: ' . $this->meeting->title
            : 'Meeting scheduled: ' . $this->meeting->title;

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name)
            ->line($this->reminder
                ? 'This meeting is in about 24 hours.'
                : 'A meeting has been scheduled in ' . $team . '.')
            ->line($this->meeting->title)
            ->line($when);

        if ($this->meeting->agenda) {
            $mail->line('Agenda: ' . $this->meeting->agenda);
        }

        if ($this->meeting->location) {
            $mail->line('Place: ' . $this->meeting->location);
        }

        if ($this->meeting->meet_link) {
            $mail->action('Open meeting link', $this->meeting->meet_link);
        }

        return $mail->action('View in Ajo', route('teams.meetings.index', $this->meeting->community->slug));
    }

    public function toArray(object $notifiable): array
    {
        $when = $this->meeting->starts_at->format('D d M, g:i A');
        $prefix = $this->reminder ? 'Reminder: ' : 'Meeting: ';

        return [
            'message' => $prefix . $this->meeting->title . ' · ' . $when,
            'url' => route('teams.meetings.index', $this->meeting->community->slug),
        ];
    }
}