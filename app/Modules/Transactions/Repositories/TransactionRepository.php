<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Repositories;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\DTOs\TransactionDTO;
use App\Modules\Transactions\Contracts\TransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getAllForUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        // Start with a query that only gets the user's transactions
        $query = Transaction::query()->where('author_id', $userId);

        // Log the initial query
        \Log::info('Initial query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'user_id' => $userId
        ]);

        if (isset($filters['type'])) {
            if ($filters['type'] === 'income') {
                $query->where('amount', '>=', 0);
            } elseif ($filters['type'] === 'expense') {
                $query->where('amount', '<', 0);
            }
        }

        if (isset($filters['amount_min'])) {
            $minAmount = floatval($filters['amount_min']);
            \Log::info('Min amount', ['min' => $minAmount]);
            $query->where(function($query) use ($minAmount) {
                $query->where('amount', '>=', $minAmount)
                      ->orWhere('amount', '<=', -$minAmount);
            });
        }

        if (isset($filters['amount_max'])) {
            $maxAmount = floatval($filters['amount_max']);
            \Log::info('Max amount', ['max' => $maxAmount]);
            $query->where(function($query) use ($maxAmount) {
                $query->where('amount', '<=', $maxAmount)
                      ->where('amount', '>=', -$maxAmount);
            });
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['sort'])) {
            $direction = $filters['sort']['direction'] ?? 'desc';
            $field = $filters['sort']['field'] ?? 'created_at';

            if ($field === 'amount') {
                $query->orderByRaw('CASE WHEN amount < 0 THEN -amount ELSE amount END ' . $direction);
            } else {
                $query->orderBy($field, $direction);
            }
        }

        // Log the final SQL query
        \Log::info('Final query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'user_id' => $userId
        ]);

        $result = $query->paginate(15);

        // Log the result count
        \Log::info('Query result', [
            'total' => $result->total(),
            'items' => $result->items()
        ]);

        return $result;
    }

    public function create(TransactionDTO $dto): Transaction
    {
        return Transaction::create([
            'title' => $dto->title,
            'amount' => $dto->amount,
            'author_id' => $dto->authorId,
        ]);
    }

    public function delete(int $transactionId): bool
    {
        $transaction = Transaction::findOrFail($transactionId);

        return $transaction->delete();
    }

    public function getSummary(int $userId, ?array $dateRange = null): array
    {
        // Base query for user's transactions
        $baseQuery = Transaction::query()->where('author_id', $userId);

        // Apply date range filters if provided
        if ($dateRange) {
            if (isset($dateRange['start'])) {
                $baseQuery->whereDate('created_at', '>=', $dateRange['start']);
            }
            if (isset($dateRange['end'])) {
                $baseQuery->whereDate('created_at', '<=', $dateRange['end']);
            }
        }

        // Get total income (positive amounts)
        $totalIncome = (int) (clone $baseQuery)
            ->where('amount', '>=', 0)
            ->sum('amount');

        // Get total expenses (negative amounts)
        $totalExpense = (int) abs((clone $baseQuery)
            ->where('amount', '<', 0)
            ->sum('amount'));

        // Get total count of transactions
        $count = (clone $baseQuery)->count();

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'count' => $count
        ];
    }

    public function getTransaction(int $id)
    {
        return Transaction::findOrFail($id);
    }

    public function getTotalIncomeForUser(int $userId): float
    {
        return (float) Transaction::where('author_id', $userId)
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    public function getTotalExpenseForUser(int $userId): float
    {
        return Transaction::where('author_id', $userId)
            ->where('amount', '<', 0)
            ->sum('amount');
    }
}
