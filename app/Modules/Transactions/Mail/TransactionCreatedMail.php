<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Mail;

use App\Modules\Transactions\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TransactionCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Transaction $transaction
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Transaction Created',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.transaction-created',
            with: [
                'type' => $this->transaction->type->label(),
                'amount' => abs((float)$this->transaction->amount),
                'title' => $this->transaction->title,
                'date' => $this->transaction->created_at->format('Y-m-d H:i:s')
            ],
        );
    }
}
