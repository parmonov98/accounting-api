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
    public function delete(int $transactionId, ?int $userId): bool
    {
        try {
            return $this->repository->delete($transactionId);
        } catch (ModelNotFoundException) {
            throw new TransactionException("Transaction not found:" . $transactionId);
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
        $transactions = $this->repository->getAllForUser($userId, ['date_range' => $dateRange]);
        
        $income = $transactions->where('type', 'income')->sum('amount');
        $expense = $transactions->where('type', 'expense')->sum('amount');
        
        return [
            'total_income' => Currency::format($income),
            'total_expense' => Currency::format($expense),
            'transaction_count' => $transactions->count(),
        ];
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
        return [
            'income' => $this->repository->getTotalIncomeForUser($userId),
            'expense' => $this->repository->getTotalExpenseForUser($userId)
        ];
    }

    public function getBalance(int $userId): array
    {
        $totalIncome = $this->repository->getTotalIncomeForUser($userId);
        $totalExpense = abs($this->repository->getTotalExpenseForUser($userId));

        $balanceEur = $totalIncome - $totalExpense;
        $balanceUsd = Currency::driver()->getRate('EUR', 'USD') * $balanceEur;

        return [
            'EUR' => $balanceEur,
            'USD' => $balanceUsd
        ];
    }

    public function destroy(int $id): bool
    {
        try {
            return $this->repository->delete($id);
        } catch (ModelNotFoundException $e) {
            throw new TransactionException('Transaction not found', previous: $e);
        }
    }

}
