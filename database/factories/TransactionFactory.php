<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Transactions\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'amount' => $this->faker->randomFloat(2, -5000, 5000),
            'author_id' => User::factory(),
        ];
    }

    public function income(): self
    {
        return $this->state(fn (array $attributes) => [
            'amount' => abs($this->faker->randomFloat(2, 100, 5000)),
        ]);
    }

    public function expense(): self
    {
        return $this->state(fn (array $attributes) => [
            'amount' => -abs($this->faker->randomFloat(2, 100, 5000)),
        ]);
    }
}
