<?php

namespace Tests\Browser;

use App\Email;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\ShowEmail;
use Tests\DuskTestCase;

class DeleteEmailTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testDeleteEmail()
    {
        $email = Email::factory()->create();

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit('/')
                    ->waitForText('1 emails')
                    ->assertSee($email->subject)
                    ->visit(new ShowEmail($email))
                    ->assertSeeEmail()
                    ->delete()
                    ->assertPathIs('/');
        });
    }
}
