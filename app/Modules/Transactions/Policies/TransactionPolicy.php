<?php

namespace App\Modules\Transactions\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Transactions\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        Log::info('Checking view permission', [
            'user_id' => $user->id,
            'transaction_user_id' => $transaction->author_id,
            'transaction_id' => $transaction->id
        ]);
        return $user->id === $transaction->author_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        Log::info('Checking delete permission', [
            'user_id' => $user->id,
            'transaction_user_id' => $transaction->author_id,
            'transaction_id' => $transaction->id
        ]);
        return $user->id === $transaction->author_id;
    }
}
