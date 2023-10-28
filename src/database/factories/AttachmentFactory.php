<?php

namespace Database\Factories;

use App\Attachment;
use App\Email;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attachment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email_id' => function () {
                return Email::factory()->create()->id;
            },
            'headers_hashed' => 'HASHXXXXXXX',
            'file_name' => 'test.pdf',
            'content_type' => 'application/pdf',
            'size_in_bytes' => 100,
        ];
    }
}