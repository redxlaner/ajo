<?php

namespace App\Notifications;

use App\Models\WelfareRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelfareRequestStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(public WelfareRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
{
    $status = $this->request->status;
    $mail = (new MailMessage)->greeting('Hello ' . $notifiable->name . ',');

    if ($status === 'approved') {
        $mail->subject('Welfare Request Approved – Awaiting Payment')
            ->line('Your welfare request has been **approved**.')
            ->line('**Type:** ' . ucfirst($this->request->request_type))
            ->line('**Amount to be paid:** ₦' . number_format($this->request->approved_amount, 2))
            ->line('The Treasurer will process the payment to your registered bank account shortly.')
            ->action('View Dashboard', url('/dashboard'));
    } elseif ($status === 'rejected') {
        $mail->subject('Welfare Request Rejected')
            ->line('Your welfare request has been **rejected**.')
            ->line('**Type:** ' . ucfirst($this->request->request_type))
            ->line('**Amount Requested:** ₦' . number_format($this->request->amount_requested, 2));

        if ($this->request->rejection_reason) {
            $mail->line('**Reason:** ' . $this->request->rejection_reason);
        }

        $mail->action('View Dashboard', url('/dashboard'));
    } elseif ($status === 'disbursed') {
        $mail->subject('Welfare Payment Disbursed')
            ->line('Your welfare payment has been **disbursed**.')
            ->line('**Type:** ' . ucfirst($this->request->request_type))
            ->line('**Amount Paid:** ₦' . number_format($this->request->approved_amount, 2))
            ->line('Please check your bank account. Contact the Treasurer if you do not receive it within 24–48 hours.')
            ->action('View Dashboard', url('/dashboard'));
    } else {
        $mail->subject('Welfare Request Update')
            ->line('There is an update on your welfare request.')
            ->line('**Status:** ' . ucfirst($status))
            ->action('View Dashboard', url('/dashboard'));
    }

    return $mail->line('Thank you for using Course Purse.');
}

public function toArray(object $notifiable): array
{
    $messages = [
        'approved'  => 'Your welfare request was approved. Amount: ₦' . number_format($this->request->approved_amount ?? 0, 2),
        'rejected'  => 'Your welfare request was rejected.',
        'disbursed' => 'Your welfare payment of ₦' . number_format($this->request->approved_amount ?? 0, 2) . ' has been disbursed.',
    ];

    return [
        'message' => $messages[$this->request->status] ?? 'Welfare request update: ' . $this->request->status,
        'url' => route('dashboard'),
    ];
}
}