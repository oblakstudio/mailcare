<?php

namespace Database\Factories;

use App\Sender;
use Illuminate\Database\Eloquent\Factories\Factory;

class SenderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sender::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email' => $this->faker->email,
            'display_name' => $this->faker->name,
            'local_part' => '',
            'domain' => '',
        ];
    }
}