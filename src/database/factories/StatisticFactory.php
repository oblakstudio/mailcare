<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StatisticFactory extends Factory
{
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
