<?php

namespace Database\Factories;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition()
    {
        return [
            'stock_id' => $this->faker->numberBetween(1,3),
            'trader_id' => $this->faker->numberBetween(1,3),
            'amount' => $this->faker->numberBetween(1,200),
        ];
    }
}
