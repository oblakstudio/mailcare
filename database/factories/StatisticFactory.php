<?php

namespace Database\Factories;

use App\Statistic;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatisticFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Statistic::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_at' => $this->faker->date(),
            'emails_received' => $this->faker->numberBetween(10, 50),
            'inboxes_created' => $this->faker->numberBetween(2, 20),
            'storage_used' => $this->faker->numberBetween(10000000, 50000000),
        ];
    }
}