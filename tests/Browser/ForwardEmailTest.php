<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Browser\Pages\Automations;
use App\User;

class ForwardEmailTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testCreateWebhook()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Automations)
                    ->createWebhook()
        			->waitForText('1 automations')
        			->editWebhook()
        			->waitForText('1 automations');
        });
    }

    public function testCreateForwardingAddresse()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Automations)
                    ->createForward()
        			->waitForText('1 automations')
        			->editForward()
        			->waitForText('1 automations');
        });
    }
}
