<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Contracts;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\DTOs\TransactionDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    public function getAllForUser(int $userId, array $filters = []): LengthAwarePaginator;
    
    public function create(TransactionDTO $dto): Transaction;
    
    public function delete(int $transactionId, int $userId): bool;
    
    public function getSummary(int $userId, ?array $dateRange = null): array;
}
