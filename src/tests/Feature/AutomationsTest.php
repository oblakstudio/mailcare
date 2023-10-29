<?php

namespace Tests\Feature;

use App\Attachment;
use App\Automation;
use App\Email;
use App\Events\EmailReceived;
use App\Http\Resources\EmailResource;
use App\Inbox;
use App\Listeners\AutomationListener;
use App\Sender;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AutomationsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mailcare.automations' => true]);
    }

    private function handleAutomationListener($email)
    {
        $event = new EmailReceived($email);
        $listener = resolve(AutomationListener::class);
        $listener->handle($event);
    }

    private function assertAutomationNotTriggered($automation)
    {
        $this->assertEquals(0, $automation->fresh()->emails_received);
        Http::assertNothingSent();
    }

    private function assertAutomationTriggered($automation, $hits, $email)
    {
        $headers = [];
        $headers['X-MailCare-Title'] = $automation->title;
        if ($automation->action_secret_token) {
            $headers['X-MailCare-Secret-Token'] = $automation->action_secret_token;
        }
        foreach ($hits as $hit) {
            $headers['X-MailCare-'.$hit] = 'HIT';
        }

        $this->assertEquals(1, $automation->fresh()->emails_received);
        Http::assertSent(function (Request $request) use ($automation, $headers, $email) {
            return $request->url() == $automation->action_url
                    && $request->method() == 'POST'
                    && $request->hasHeaders($headers)
                    && $request->body() == (new EmailResource($email))->response()->content();
        });
    }

    public function subjectProvider()
    {
        return [
            ['My first email'],
            ['my First Email'],
            ['first'],
            ['my.*email'],
        ];
    }

    /**
     * @dataProvider subjectProvider
     */
    public function testSubjectDoesMatch($subjectFilter)
    {
        Http::fake();

        $automation = Automation::factory()->create([
            'subject' => $subjectFilter,
        ]);
        $email = Email::factory()->create([
            'subject' => 'My first email',
        ]);

        $this->handleAutomationListener($email);

        $this->assertAutomationTriggered($automation, ['Subject'], $email);
    }

    public function senderProvider()
    {
        return [
            ['test@example\.com'],
            ['TEST@example\.com'],
            ['@example'],
            ['test@.*\.com'],
        ];
    }

    /**
     * @dataProvider senderProvider
     */
    public function testSenderDoesMatch($senderFilter)
    {
        Http::fake();

        $sender = Sender::factory()->create(['email' => 'test@example.com']);
        $email = Email::factory()->create([
            'sender_id' => $sender->id,
        ]);

        $automation = Automation::factory()->create([
            'sender' => $senderFilter,
        ]);

        $this->handleAutomationListener($email);

        $this->assertAutomationTriggered($automation, ['Sender'], $email);
    }

    public function inboxProvider()
    {
        return [
            ['test@example\.com'],
            ['TEST@example\.com'],
            ['@example'],
            ['test@.*\.com'],
        ];
    }

    /**
     * @dataProvider inboxProvider
     */
    public function testInboxDoesMatch($inboxFilter)
    {
        Http::fake();

        $inbox = Inbox::factory()->create(['email' => 'test@example.com']);
        $email = Email::factory()->create([
            'inbox_id' => $inbox->id,
        ]);

        $automation = Automation::factory()->create([
            'inbox' => $inboxFilter,
        ]);

        $this->handleAutomationListener($email);
        $this->assertAutomationTriggered($automation, ['Inbox'], $email);
    }

    public function testHasAttachmentDoesMatch()
    {
        Http::fake();

        $email = Email::factory()->create();
        $attachment = Attachment::factory()->create(['email_id' => $email->id]);

        $automation = Automation::factory()->create([
            'has_attachments' => true,
        ]);

        $this->handleAutomationListener($email);
        $this->assertAutomationTriggered($automation, ['Has-Attachments'], $email);
    }

    public function testSubjectDoesMatchButSenderNot()
    {
        Http::fake();

        $sender = Sender::factory()->create(['email' => 'test@example.com']);

        $automation = Automation::factory()->create([
            'subject' => 'My first email',
            'sender' => 'othertest@example\.com',
        ]);
        $email = Email::factory()->create([
            'subject' => 'My first email',
            'sender_id' => $sender->id,
        ]);

        $this->handleAutomationListener($email);
        $this->assertAutomationNotTriggered($automation);
    }

    public function testEmptyDoesMatch()
    {
        Http::fake();

        $automation = Automation::factory()->create();
        $email = Email::factory()->create();

        $this->handleAutomationListener($email);
        $this->assertAutomationTriggered($automation, [], $email);
    }

    public function testSecretTokenSendedWhenAsked()
    {
        Http::fake();

        $automation = Automation::factory()->create([
            'action_secret_token' => 'secret1234',
        ]);
        $email = Email::factory()->create();

        $this->handleAutomationListener($email);
        $this->assertAutomationTriggered($automation, [], $email);
    }

    public function wrongSubjectProvider()
    {
        return [
            ['My test email'],
            ['test$'],
            ['^My email$'],
        ];
    }

    /**
     * @dataProvider wrongSubjectProvider
     */
    public function testSubjectDoesntMatch($subjectFilter)
    {
        Http::fake();

        $automation = Automation::factory()->create([
            'subject' => $subjectFilter,
        ]);
        $email = Email::factory()->create([
            'subject' => 'My first email',
        ]);

        $this->handleAutomationListener($email);
        $this->assertAutomationNotTriggered($automation);
    }

    public function wrongSenderProvider()
    {
        return [
            ['othertest@example\.com'],
            ['example\.net$'],
            ['mytest.*'],
        ];
    }

    /**
     * @dataProvider wrongSenderProvider
     */
    public function testSenderDoesntMatch($senderFilter)
    {
        Http::fake();

        $sender = Sender::factory()->create(['email' => 'test@example.com']);
        $email = Email::factory()->create([
            'sender_id' => $sender->id,
        ]);

        $automation = Automation::factory()->create([
            'sender' => $senderFilter,
        ]);

        $this->handleAutomationListener($email);
        $this->assertAutomationNotTriggered($automation);
    }

    public function wrongInboxProvider()
    {
        return [
            ['othertest@example\.com'],
            ['example\.net$'],
            ['mytest.*'],
        ];
    }

    /**
     * @dataProvider wrongInboxProvider
     */
    public function testInboxDoesntMatch($inboxFilter)
    {
        Http::fake();

        $inbox = Inbox::factory()->create(['email' => 'test@example.com']);
        $email = Email::factory()->create([
            'inbox_id' => $inbox->id,
        ]);

        $automation = Automation::factory()->create([
            'inbox' => $inboxFilter,
        ]);

        $this->handleAutomationListener($email);
        $this->assertAutomationNotTriggered($automation);
    }

    public function testHasntAttachmentDoesntMatch()
    {
        Http::fake();

        $email = Email::factory()->create();

        $automation = Automation::factory()->create([
            'has_attachments' => true,
        ]);

        $this->handleAutomationListener($email);
        $this->assertAutomationNotTriggered($automation);
    }

    public function testDeleteEmailAfterProcessingAutomation()
    {
        Http::fake();

        $automation = Automation::factory()->create([
            'subject' => 'Order completed',
            'action_delete_email' => true,
        ]);
        $email = Email::factory()->create([
            'subject' => 'Order completed',
        ]);

        $this->assertCount(1, Email::all());
        $this->handleAutomationListener($email);

        $this->assertAutomationTriggered($automation, ['Subject'], $email);
        $this->assertCount(0, Email::all());
    }

    public function testDontDeleteEmailIfAutomationDoesntMatch()
    {
        Http::fake();

        $automation = Automation::factory()->create([
            'subject' => 'Order cancelled',
            'action_delete_email' => false,
        ]);
        $automation = Automation::factory()->create([
            'subject' => 'Order completed',
        ]);
        $email = Email::factory()->create([
            'subject' => 'Order completed',
        ]);

        $this->assertCount(1, Email::all());
        $this->handleAutomationListener($email);

        $this->assertAutomationTriggered($automation, ['Subject'], $email);
        $this->assertCount(1, Email::all());
    }

    public function testDontDeleteEmailAfterProcessingAutomation()
    {
        Http::fake();

        $automation = Automation::factory()->create([
            'subject' => 'Order completed',
            'action_delete_email' => false,
        ]);
        $email = Email::factory()->create([
            'subject' => 'Order completed',
        ]);

        $this->handleAutomationListener($email);

        $this->assertCount(1, Email::all());
        $this->assertAutomationTriggered($automation, ['Subject'], $email);
        $this->assertCount(1, Email::all());
    }

    public function testFailed()
    {
        Http::fakeSequence()
            ->push('Hello World', 500)
            ->push('Hello World', 200)
            ->push('Hello World', 400);

        $automation = Automation::factory()->create([
            'subject' => 'Order completed',
        ]);

        $email = Email::factory()->create([
            'subject' => 'Order completed',
        ]);
        $this->handleAutomationListener($email);
        $this->assertTrue($automation->fresh()->in_error);

        $email = Email::factory()->create([
            'subject' => 'Order completed',
        ]);
        $this->handleAutomationListener($email);
        $this->assertFalse($automation->fresh()->in_error);

        $email = Email::factory()->create([
            'subject' => 'Order completed',
        ]);
        $this->handleAutomationListener($email);
        $this->assertTrue($automation->fresh()->in_error);
    }
}
