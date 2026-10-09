<?php

namespace App\Notifications;

use App\Models\MonthlyContribution;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContributionSubmitted extends Notification
{
    use Queueable;

    public function __construct(public MonthlyContribution $contribution) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Contribution Submitted')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->contribution->user->name . ' has submitted a new contribution.')
            ->line('**Amount:** ₦' . number_format($this->contribution->total_amount, 2))
            ->action('Review Contributions', url('/contributions/manage'))
            ->line('Please review the proof of payment and take action.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->contribution->user->name . ' submitted a contribution of ₦' . number_format($this->contribution->total_amount, 2),
            'url' => route('contributions.manage'),
        ];
    }
}