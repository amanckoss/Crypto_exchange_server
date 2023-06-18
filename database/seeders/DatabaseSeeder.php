<?php

namespace Database\Seeders;

use App\Models\OrderBook;
use App\Models\Stock;
use App\Models\User;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * The current Faker instance.
     *
     * @var Generator
     */
    protected Generator $faker;

    /**
     * Create a new seeder instance.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Get a new Faker instance.
     *
     * @return Generator
     * @throws BindingResolutionException
     */
    protected function withFaker(): Generator
    {
        return Container::getInstance()->make(Generator::class);
    }


    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         User::factory(10)->create();
         Stock::factory(10)->create();
        for ($i = 1; $i <= 10; $i++) {

            $median_price = rand(1000, 100000) / 100;;
            for ($t = 0; $t < 100; $t++) {

                $operation = $this->faker->randomElement($array = array('sell', 'buy'));
                $price = $median_price + rand(100, 300) / 100;
                if ($operation == 'sell') {
                    $price += 5;
                }
                OrderBook::factory()->count(1)->create([
                    'stock_id' => $i,
                    'operation' => $operation,
                    'price' => $price,
                ]);
            }
        }
    }
}
