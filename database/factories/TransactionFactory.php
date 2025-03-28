<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'type' => fake()->randomElement(['Credit', 'Debit']),
            'amount' => fake()->randomFloat(2, 0.01, 1000),
            'description' => fake()->sentence(),
        ];
    }
} 