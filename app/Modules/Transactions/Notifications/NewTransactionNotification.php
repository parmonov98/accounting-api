<?php

namespace App\Modules\Transactions\Notifications;

use App\Modules\Transactions\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewTransactionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private Transaction $transaction
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $type = ucfirst($this->transaction->type);
        $amount = number_format($this->transaction->amount, 2);
        
        return (new MailMessage)
            ->subject("New {$type} Transaction")
            ->line("A new {$this->transaction->type} transaction has been recorded.")
            ->line("Amount: {$amount} {$this->transaction->currency}")
            ->line($this->transaction->description ?? 'No description provided.')
            ->action('View Transaction', url('/transactions/' . $this->transaction->id));
    }
}
