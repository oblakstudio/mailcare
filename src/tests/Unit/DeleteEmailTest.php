<?php

namespace Tests\Unit;

use App\Email;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DeleteEmailTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_can_soft_delete_email()
    {
        $this->artisan(
            'mailcare:email-receive',
            ['file' => 'tests/storage/email_with_attachment.eml']
        );

        $this->assertCount(1, Email::all());
        $this->assertCount(1, Email::first()->attachments()->get());

        $email = Email::first();
        $response = $this->json('DELETE', 'api/emails/'.$email->id);

        $response->assertStatus(200);
        $this->assertCount(0, Email::all());
        $this->assertCount(1, Email::withTrashed()->get());
        $this->assertCount(1, Email::withTrashed()->first()->attachments()->get());
    }
}
