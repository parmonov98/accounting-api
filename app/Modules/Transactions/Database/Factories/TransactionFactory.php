<?php

namespace App\Modules\Transactions\Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'amount' => $this->faker->numberBetween(100, 10000),
            'author_id' => User::factory(),
        ];
    }
}
