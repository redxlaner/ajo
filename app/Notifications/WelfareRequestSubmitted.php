<?php

namespace App\Notifications;

use App\Models\WelfareRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelfareRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(public WelfareRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];   // ← now sends both in-app + email
    }

    public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('New Welfare Request – Action Required')
        ->greeting('Hello ' . $notifiable->name . ',')
        ->line('A new welfare request has been submitted and needs your review.')
        ->line('**Member:** ' . $this->request->user->name)
        ->line('**Type:** ' . ucfirst($this->request->request_type))
        ->line('**Amount Requested:** ₦' . number_format($this->request->amount_requested, 2))
        ->action('Review Request', url('/welfare/manage'))
        ->line('Please review and approve or reject this request.');
}

public function toArray(object $notifiable): array
{
    return [
        'message' => $this->request->user->name . ' submitted a ' . $this->request->request_type . ' request of ₦' . number_format($this->request->amount_requested, 2),
        'url' => route('welfare.manage'),
    ];
}
}