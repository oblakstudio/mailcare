<?php

namespace Tests\Feature;

use App\Events\EmailReceived;
use App\Listeners\AutomationListener;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use Tests\TestCase;

class AutomationListenerTest extends TestCase
{
    use DatabaseMigrations;

    public function testEventEmailReceivedWhenNewEmail()
    {
        $this->expectsEvents(EmailReceived::class);

        $this->artisan('mailcare:email-receive', [
            'file' => 'tests/storage/email_without_attachment.eml',
        ])
        ->expectsOutput('Receiving email ...')
        ->assertExitCode(0);
    }

    public function testAutomationListenerHasBeenCalled()
    {
        $listener = Mockery::spy(AutomationListener::class);
        app()->instance(AutomationListener::class, $listener);

        $this->artisan('mailcare:email-receive', [
            'file' => 'tests/storage/email_without_attachment.eml',
        ])
        ->expectsOutput('Receiving email ...')
        ->assertExitCode(0);

        $listener->shouldHaveReceived('handle')->with(Mockery::on(function ($event) {
            return $event->email->subject == 'My first email';
        }));
    }
}
