<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Services;

use App\Modules\Transactions\Contracts\TransactionRepositoryInterface;
use App\Modules\Transactions\Contracts\TransactionServiceInterface;
use App\Modules\Transactions\DTOs\TransactionDTO;
use App\Modules\Transactions\Events\TransactionCreated;
use App\Modules\Transactions\Exceptions\TransactionException;
use App\Modules\Transactions\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Modules\Transactions\Mail\TransactionCreatedMail;
use Illuminate\Pagination\LengthAwarePaginator;

final class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private readonly TransactionRepositoryInterface $repository
    ) {}

    /**
     * Get all transactions for a user with optional filters
     */
    public function getAllForUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getAllForUser($userId, $filters);
    }

    /**
     * Create a new transaction
     * 
     * @throws TransactionException
     */
    public function create(TransactionDTO $dto): Transaction
    {
        try {
            $transaction = $this->repository->create($dto);
            
            // Log the transaction
            Log::info('Transaction created', [
                'id' => $transaction->id,
                'author_id' => $transaction->author_id,
                'amount' => $transaction->amount,
                'title' => $transaction->title
            ]);

            // Send email notification
            Mail::to($transaction->author->email)->queue(new TransactionCreatedMail($transaction));

            // Broadcast the event
            Event::dispatch(new TransactionCreated($transaction));

            return $transaction;
        } catch (\Exception $e) {
            throw new TransactionException(
                'Failed to create transaction: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Delete a transaction
     * 
     * @throws TransactionException
     */
    public function delete(int $transactionId, int $userId): bool
    {
        try {
            return $this->repository->delete($transactionId, $userId);
        } catch (\Exception $e) {
            throw new TransactionException(
                'Failed to delete transaction: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Get transaction summary for a user
     * 
     * @throws TransactionException
     */
    public function getSummary(int $userId, ?array $dateRange = null): array
    {
        try {
            return $this->repository->getSummary($userId, $dateRange);
        } catch (\Exception $e) {
            throw new TransactionException(
                'Failed to get transaction summary: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
}
