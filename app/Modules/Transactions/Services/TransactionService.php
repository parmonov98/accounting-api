<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Services;

use App\Modules\Currency\Facades\Currency;
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

final readonly class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private TransactionRepositoryInterface $repository
    ) {
    }

    /**
     * Get all transactions for a user with optional filters
     */
    public function getAllForUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        try {
            return $this->repository->getAllForUser($userId, $filters);
        } catch (\Exception $e) {
            Log::error('Service: Error getting transactions', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'filters' => $filters
            ]);
            throw new TransactionException(
                'Failed to get transactions: ' . $e->getMessage(),
                previous: $e
            );
        }
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

            // Dispatch event
            Event::dispatch(new TransactionCreated($transaction));

            // Send notification
            Mail::to($transaction->author->email)
                ->queue(new TransactionCreatedMail($transaction));

            return $transaction;
        } catch (\Exception $e) {
            Log::error('Service: Error creating transaction', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $dto->authorId
            ]);
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
    public function delete(int $id): bool
    {
        try {
            return $this->repository->delete($id);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Service: Error deleting transaction', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id
            ]);
            throw new TransactionException(
                'Failed to delete transaction: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Get transaction summary for a user within an optional date range
     *
     * @param int $userId
     * @param array|null $dateRange
     * @return array
     */
    public function getSummary(int $userId, ?array $dateRange = null): array
    {
        try {
            return $this->repository->getSummary($userId, $dateRange);
        } catch (\Exception $e) {
            Log::error('Service: Error getting transaction summary', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'date_range' => $dateRange
            ]);
            throw new TransactionException(
                'Failed to get transaction summary: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Get detailed transaction summary for a user with date range filtering
     *
     * @throws TransactionException
     */
    public function getTransactionSummaryByDateRange(int $userId, ?array $dateRange = null): array
    {
        try {
            return $this->getSummary($userId, $dateRange);
        } catch (\Exception $e) {
            Log::error('Service: Error getting transaction summary', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId
            ]);
            throw new TransactionException(
                'Failed to get transaction summary: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Get total income and expense summary for a user
     */
    public function getIncomeExpenseSummary(int $userId): array
    {
        try {
            return [
                'income' => $this->repository->getTotalIncomeForUser($userId),
                'expense' => $this->repository->getTotalExpenseForUser($userId)
            ];
        } catch (\Exception $e) {
            Log::error('Service: Error getting income expense summary', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId
            ]);
            throw new TransactionException(
                'Failed to get income expense summary: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    public function getBalance(int $userId): array
    {
        try {
            $totalIncome = $this->repository->getTotalIncomeForUser($userId);
            $totalExpense = $this->repository->getTotalExpenseForUser($userId);

            return [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'balance' => $totalIncome - $totalExpense
            ];
        } catch (\Exception $e) {
            Log::error('Service: Error getting balance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId
            ]);
            throw new TransactionException(
                'Failed to get balance: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    public function destroy(int $id): Transaction
    {
        try {
            $transaction = $this->repository->getTransaction($id);
            $this->repository->delete($id);
            return $transaction;
        } catch (\Exception $e) {
            Log::error('Service: Error destroying transaction', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id
            ]);
            throw new TransactionException(
                'Failed to destroy transaction: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

}
