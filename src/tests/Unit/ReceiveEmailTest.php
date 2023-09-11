<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReceiveEmailTest extends TestCase
{
    use DatabaseMigrations;

    public function test_system_can_receive_email_from_file()
    {
        $this->artisan(
        	'mailcare:email-receive',
        	['file' => 'tests/storage/email_without_attachment.eml']
        )->assertExitCode(0);
        $this->assertDatabaseHas('emails', ['subject' => 'My first email']);
    }
}
