<?php

namespace Database\Factories;

use App\Models\OrderBook;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderBookFactory extends Factory
{
    protected $model = OrderBook::class;

    public function definition()
    {
        return [
            'stock_id' => 1,
            'trader_id' => $this->faker->numberBetween(1,10),
            'amount' => $this->faker->numberBetween(1,20),
            'operation' => 'sell',
            'price' => 0,
        ];
    }

}
