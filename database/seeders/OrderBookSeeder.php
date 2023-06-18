<?php

namespace Database\Seeders;

use App\Models\OrderBook;
use Database\Factories\OrderBookFactory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories;

class OrderBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderBook::factory()->count(30)->create();
    }
}
