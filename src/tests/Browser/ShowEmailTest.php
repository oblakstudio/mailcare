<?php

namespace Tests\Browser;

use App\Email;
use App\Inbox;
use App\Sender;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\ShowEmail;
use Tests\DuskTestCase;

class ShowEmailTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testShowEmailWithoutAttachment()
    {
        $this->artisan(
            'mailcare:email-receive',
            ['file' => 'tests/storage/email_without_attachment.eml']
        )->assertExitCode(0);

        $email = Email::first();

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit('/')
                    ->waitForText('1 emails')
                    ->clickLink('My first email')
                    ->on(new ShowEmail($email))
                    ->assertSeeEmail()
                    ->waitFor('li.is-active[data-label="Html"]')
                    ->withinFrame('[name=iframe-body]', function ($browser) {
                        $browser->assertSeeLink('mailcare.io')
                                ->assertSourceHas('My name is <strong>vincent</strong>.');
                    })
                    ->clickLink('Text')
                    ->waitFor('li.is-active[data-label="Text"]')
                    ->withinFrame('[name=iframe-body]', function ($browser) {
                        $browser->assertSee('Welcome to mailcare.io')
                                ->assertSee('sorry no link in plain text');
                    })
                    ->clickLink('Raw')
                    ->waitFor('li.is-active[data-label="Raw"]')
                    ->withinFrame('[name=iframe-body]', function ($browser) {
                        $browser->waitForText('Content-Transfer-Encoding')
                                ->assertSee('sorry no link in plain text')
                                ->assertSee('My name is <strong>vincent</strong>.', false)
                                ->assertSee('Content-Transfer-Encoding');
                    });
        });
    }

    public function testShowEmailWithAttachment()
    {
        $this->artisan(
            'mailcare:email-receive',
            ['file' => 'tests/storage/email_with_attachment.eml']
        )->assertExitCode(0);
        
        $email = Email::first();

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit('/')
                    ->waitForText('1 email')
                    ->clickLink('Logo of MailCare')
                    ->on(new ShowEmail($email))
                    ->assertSeeEmail()
                    ->waitFor('li.is-active[data-label="Html"]')
                    ->withinFrame('[name=iframe-body]', function ($browser) {
                        $browser->assertSourceHas('Please find attached the file you requested.<br>');
                    })
                    ->clickLink('Text')
                    ->waitFor('li.is-active[data-label="Text"]')
                    ->withinFrame('[name=iframe-body]', function ($browser) {
                        $browser->assertSee('Please find attached the file you requested.');
                    })
                    ->clickLink('Raw')
                    ->waitFor('li.is-active[data-label="Raw"]')
                    ->withinFrame('[name=iframe-body]', function ($browser) {
                        $browser->waitForText('logo-mailcare-renard.png')
                                ->assertSee('Please find attached the file you requested.');
                    });
        });
    }

    public function testFavoriteEmail()
    {
        $emailOne = Email::factory()->create(['subject' => 'My first email']);
        $emailTwo = Email::factory()->create(['subject' => 'My second email']);

        $this->browse(function (Browser $browser) use ($emailOne, $emailTwo) {
            $browser->visit('/')
                    ->waitForText('2 emails')
                    ->clickLink($emailTwo->subject)
                    ->on(new ShowEmail($emailTwo))
                    ->assertSeeEmail()
                    ->favorite()
                    ->visit('/')
                    ->clickLink('Favorite')
                    ->waitForText('1 emails')
                    ->assertSeeLink($emailTwo->subject)
                    ->assertDontSeeLink($emailOne->subject);
        });
    }

    public function testUnreadEmail()
    {
        $emailOne = Email::factory()->create(['subject' => 'My first email']);
        $emailTwo = Email::factory()->create(['subject' => 'My second email']);

        $this->browse(function (Browser $browser) use ($emailOne, $emailTwo) {
            $browser->visit('/')
                    ->waitForText('2 emails')
                    ->clickLink($emailOne->subject)
                    ->on(new ShowEmail($emailOne))
                    ->assertSeeEmail()
                    ->visit('/')
                    ->clickLink('Unread')
                    ->waitForText('1 emails')
                    ->assertSeeLink($emailTwo->subject)
                    ->assertDontSeeLink($emailOne->subject);
        });
    }

    public function testFilterEmails()
    {
        $emailOne = Email::factory()->create(['subject' => 'My first email']);
        $emailTwo = Email::factory()->create(['subject' => 'My second email']);

        $this->browse(function (Browser $browser) use ($emailOne, $emailTwo) {
            $browser->visit('/')
                    ->waitForText('2 emails')
                    ->type('input', 'my first')
                    ->waitForText('1 emails')
                    ->assertSeeLink($emailOne->subject)
                    ->assertDontSeeLink($emailTwo->subject)
                    ->type('input', 'email')
                    ->waitForText('2 emails')
                    ->assertSeeLink($emailOne->subject)
                    ->assertSeeLink($emailTwo->subject);
        });
    }

    public function testStatistics()
    {
        $emailOne = Email::factory()->create(['subject' => 'My first email']);
        $emailTwo = Email::factory()->create(['subject' => 'My second email']);

        $this->artisan(
            'mailcare:build-statistics',
            ['date' => Carbon::now()->toDateString()]
        );

        $this->browse(function (Browser $browser) {
            $browser->visit('/statistics')
                    ->waitForText('EMAILS RECEIVED')
                    ->assertSee('2');
        });
    }

    public function testFilterBySender()
    {
        $sender = Sender::factory()->create(['email' => 'test@example.com']);

        $emails = Email::factory()->count(2)->create();
        $emailFiltered = Email::factory()->create([
            'sender_id' => $sender->id,
        ]);

        $this->browse(function (Browser $browser) use ($emailFiltered) {
            $browser->visit('/')
                    ->waitForText('3 emails')
                    ->assertSeeLink($emailFiltered->subject)
                    ->clickLink($emailFiltered->subject)
                    ->on(new ShowEmail($emailFiltered))
                    ->assertSeeEmail()
                    ->clickLink($emailFiltered->sender->email)
                    ->waitForLocation('/senders/'.$emailFiltered->sender->email)
                    ->waitForText('1 emails')
                    ->assertSee('Filtered by sender')
                    ->assertSee($emailFiltered->sender->email);
        });
    }

    public function testFilterByInbox()
    {
        $inbox = Inbox::factory()->create(['email' => 'test@example.com']);

        $emails = Email::factory()->create();
        $emailFiltered = Email::factory()->count(2)->create([
            'inbox_id' => $inbox->id,
        ])->first();

        $this->browse(function (Browser $browser) use ($emailFiltered) {
            $browser->visit('/')
                    ->waitForText('3 emails')
                    ->assertSeeLink($emailFiltered->subject)
                    ->clickLink($emailFiltered->subject)
                    ->on(new ShowEmail($emailFiltered))
                    ->assertSeeEmail()
                    ->clickLink($emailFiltered->inbox->email)
                    ->waitForLocation('/inboxes/'.$emailFiltered->inbox->email)
                    ->waitForText('2 emails')
                    ->assertSee('Filtered by inbox')
                    ->assertSee($emailFiltered->inbox->email);
        });
    }
}
