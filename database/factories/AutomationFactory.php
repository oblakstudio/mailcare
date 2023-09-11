<?php

namespace Database\Factories;

use App\Automation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AutomationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Automation::class;

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
            'action_url' => $this->faker->url
        ];
    }
}