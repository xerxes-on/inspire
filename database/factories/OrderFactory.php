<?php

namespace Database\Factories;

use App\Models\User;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date=new DateTime();

        $times =['seek',$date->modify('+1 day')->format('Y-m-d'),$date->format('Y-m-d'),$date->modify('+1 week')->format('Y-m-d')];
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'store' => $this->faker->randomElement(['Wildberries', 'Ozon']),
            'branch' => $this->faker->randomElement(['Короб', 'Казань', 'Подольск', 'Казань 2', 'Тула']),
            'type' => $this->faker->randomElement(['Короб', 'Monopolleta', 'Supersafe']),
            'coefficient' => $this->faker->randomElement(['Бесплатная', 'x1', 'x2', 'x3', 'x4', 'x5', 'x6', 'x7', 'x8', 'x9', 'x10' ]),
        'time'=> $this->faker->randomElement($times)
            ];
    }
}
