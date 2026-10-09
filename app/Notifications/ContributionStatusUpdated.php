<?php

namespace App\Notifications;

use App\Models\MonthlyContribution;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContributionStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(public MonthlyContribution $contribution) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = ucfirst($this->contribution->status);

        return (new MailMessage)
            ->subject("Your Contribution has been {$status}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("Your contribution of ₦" . number_format($this->contribution->total_amount, 2) . " has been **{$status}**.")
            ->action('View Portfolio', url('/portfolio'))
            ->line('Thank you for contributing to Course Purse.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Your contribution of ₦' . number_format($this->contribution->total_amount, 2) . ' has been ' . $this->contribution->status,
            'url' => route('portfolio'),
        ];
    }
}