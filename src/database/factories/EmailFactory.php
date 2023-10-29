<?php

namespace Database\Factories;

use App\Inbox;
use App\Sender;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sender_id' => function () {
                return Sender::factory()->create()->id;
            },
            'inbox_id' => function () {
                return Inbox::factory()->create()->id;
            },
            'subject' => $this->faker->sentence(5),
            'read' => null,
        ];
    }
}
