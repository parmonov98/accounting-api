<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Enums;

enum TransactionType: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';

    public static function fromAmount(float|int|string $amount): self
    {
        $numericAmount = is_string($amount) ? floatval($amount) : $amount;
        return $numericAmount >= 0 ? self::INCOME : self::EXPENSE;
    }

    public function label(): string
    {
        return match($this) {
            self::INCOME => 'Income',
            self::EXPENSE => 'Expense',
        };
    }
}
