<?php

namespace App\Modules\Transactions\Events;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Enums\TransactionType;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TransactionCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Transaction $transaction
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('transactions.' . $this->transaction->author_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->transaction->id,
            'title' => $this->transaction->title,
            'amount' => $this->transaction->amount,
            'type' => $this->transaction->type->value,
            'created_at' => $this->transaction->created_at,
            'updated_at' => $this->transaction->updated_at,
        ];
    }
}
