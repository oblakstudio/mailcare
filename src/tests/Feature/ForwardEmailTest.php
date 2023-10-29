<?php

namespace Tests\Feature;

use App\Automation;
use App\Mail\ForwardEmail;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForwardEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'mailcare.auth' => true,
            'mailcare.automations' => true,
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function testCreateForwardWithoutMailCareForward()
    {
        config(['mailcare.forward' => false]);
        $this->assertEquals(false, config('mailcare.forward'));

        $response = $this->json('POST', 'api/automations', [
            'title' => 'My new automation',
            'has_attachments' => false,
            'action_url' => null,
            'action_email' => 'mailcare@example.com',
            'action_delete_email' => false,
        ]);

        $response->assertStatus(422);
        $this->assertCount(0, Automation::all());
    }

    public function testCreateForwardWithMailCareForward()
    {
        config(['mailcare.forward' => true]);
        $this->assertEquals(true, config('mailcare.forward'));

        $response = $this->json('POST', 'api/automations', [
            'title' => 'My new automation',
            'has_attachments' => false,
            'post_raw' => false,
            'action_url' => null,
            'action_email' => 'mailcare@example.com',
            'action_delete_email' => false,
        ]);

        $response->assertStatus(200);
        $this->assertCount(1, Automation::all());
        $this->assertEquals('mailcare@example.com', Automation::first()->action_email);
    }

    public function testForwardingAddresse()
    {
        Mail::fake();

        config(['mailcare.forward' => true]);
        $this->assertEquals(true, config('mailcare.forward'));

        Automation::factory()->create([
            'action_url' => null,
            'action_email' => 'test@example.com',
        ]);

        Mail::assertNothingSent();
        $this->artisan('mailcare:email-receive', [
            'file' => 'tests/storage/email_without_attachment.eml',
        ]);

        Mail::assertSent(ForwardEmail::class, function ($mail) {
            return  $mail->hasTo('test@example.com');
        });
        $this->assertFalse(Automation::first()->in_error);
    }
}
