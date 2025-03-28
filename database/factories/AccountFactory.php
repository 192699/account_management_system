<?php

namespace Database\Factories;

use App\Helpers\LuhnHelper;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_name' => fake()->company() . "'s Account",
            'account_number' => LuhnHelper::generateAccountNumber(),
            'account_type' => fake()->randomElement(['Personal', 'Business']),
            'currency' => fake()->randomElement(['USD', 'EUR', 'GBP']),
            'balance' => fake()->randomFloat(2, 0, 10000),
        ];
    }
} 