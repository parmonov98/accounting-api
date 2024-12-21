<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Repositories;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\DTOs\TransactionDTO;
use App\Modules\Transactions\Contracts\TransactionRepositoryInterface;
use App\Modules\Transactions\Enums\TransactionType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getAllForUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        try {
            // Start with a query that only gets the user's transactions
            $query = Transaction::query()->where('author_id', $userId);

            if (isset($filters['type'])) {
                if ($filters['type'] === TransactionType::INCOME->value) {
                    $query->where('amount', '>=', 0);
                } elseif ($filters['type'] === TransactionType::EXPENSE->value) {
                    $query->where('amount', '<', 0);
                }
            }

            if (isset($filters['amount_min'])) {
                $minAmount = floatval($filters['amount_min']);
                $query->where(function ($query) use ($minAmount) {
                    $query->where('amount', '>=', $minAmount)
                        ->orWhere('amount', '<=', -$minAmount);
                });
            }

            if (isset($filters['amount_max'])) {
                $maxAmount = floatval($filters['amount_max']);
                $query->where(function ($query) use ($maxAmount) {
                    $query->where('amount', '<=', $maxAmount)
                        ->orWhere('amount', '>=', -$maxAmount);
                });
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            if (isset($filters['sort'])) {
                $field = $filters['sort']['field'] ?? 'created_at';
                $direction = $filters['sort']['direction'] ?? 'desc';

                if ($field === 'amount') {
                    $query->orderByRaw('ABS(amount) ' . $direction);
                } else {
                    $query->orderBy($field, $direction);
                }
            } else {
                $query->latest();
            }

            $perPage = $filters['per_page'] ?? 10;
            $page = $filters['page'] ?? 1;

            $result = $query->paginate(
                perPage: $perPage,
                page: $page
            );

            return $result;
        } catch (\Exception $e) {
            \Log::error('Error getting transactions', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'filters' => $filters
            ]);
            throw $e;
        }
    }

    public function create(TransactionDTO $dto): Transaction
    {
        try {
            return DB::transaction(function () use ($dto) {
                return Transaction::create([
                    'title' => $dto->title,
                    'amount' => $dto->amount,
                    'author_id' => $dto->authorId,
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Error creating transaction', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'dto' => $dto
            ]);
            throw $e;
        }
    }

    public function delete(int $transactionId): bool
    {
        try {
            return DB::transaction(function () use ($transactionId) {
                $transaction = Transaction::findOrFail($transactionId);
                return $transaction->delete();
            });
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error deleting transaction', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $transactionId
            ]);
            throw $e;
        }
    }

    public function getSummary(int $userId, ?array $dateRange = null): array
    {
        try {
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
            $totalIncome = (float) (clone $baseQuery)
                ->where('amount', '>=', 0)
                ->sum(DB::raw('CAST(amount AS DECIMAL(10,2))')) ?? 0;

            // Get total expenses (negative amounts) - use SQL's ABS
            $totalExpense = (float) (clone $baseQuery)
                ->where('amount', '<', 0)
                ->sum(DB::raw('ABS(CAST(amount AS DECIMAL(10,2)))')) ?? 0;

            // Get total count of transactions
            $count = (clone $baseQuery)->count();

            return [
                'total_income' => (int) round($totalIncome),
                'total_expense' => (int) round($totalExpense),
                'transaction_count' => $count
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting transaction summary', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    public function getTransaction(int $id): Transaction
    {
        try {
            return Transaction::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error getting transaction', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id
            ]);
            throw $e;
        }
    }

    public function getTotalIncomeForUser(int $userId): float
    {
        try {
            return (float) Transaction::where('author_id', $userId)
                ->where('amount', '>', 0)
                ->sum('amount');
        } catch (\Exception $e) {
            \Log::error('Error getting total income', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    public function getTotalExpenseForUser(int $userId): float
    {
        try {
            return (float) Transaction::where('author_id', $userId)
                ->where('amount', '<', 0)
                ->sum('amount');
        } catch (\Exception $e) {
            \Log::error('Error getting total expense', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }
}
