<?php

namespace App\Modules\Transactions\Listeners;

use App\Modules\Transactions\Events\TransactionCreated;
use App\Modules\Transactions\Notifications\NewTransactionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTransactionNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionCreated $event): void
    {
        $event->transaction->user->notify(new NewTransactionNotification($event->transaction));
    }
}
