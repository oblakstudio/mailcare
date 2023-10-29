<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AutomationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'title' => $this->faker->sentence(3),
            'action_url' => $this->faker->url(),
        ];
    }
}
