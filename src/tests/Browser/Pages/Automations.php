<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Automations extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/automations';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url());
    }

    public function createWebhook(Browser $browser)
    {
        $browser->click('@open-create-button');
        $browser->select('@type-of-action-field', 'webhook');
        $browser->type('@title-field', 'test webhook');
        $browser->type('@url-field', 'http://localhost/webhook/1');

        $browser->assertMissing('@email-field');
        $browser->assertPresent('@secret-field');
        $browser->assertPresent('@raw-checkbox');
        $browser->click('@create-button');
    }

    public function editWebhook(Browser $browser)
    {
        $browser->click('@open-button');
        $browser->click('@edit-button');
        $browser->assertSelected('@type-of-action-field', 'webhook');
        $browser->assertDisabled('@type-of-action-field');
        $browser->type('@title-field', 'test webhook edited');
        $browser->type('@url-field', 'http://localhost/webhook/1-edited');
        $browser->click('@save-button');
    }

    public function createForward(Browser $browser)
    {
        $browser->click('@open-create-button');
        $browser->select('@type-of-action-field', 'forwarding');
        $browser->type('@title-field', 'test forward');
        $browser->type('@email-field', 'toto@example.com');

        $browser->assertMissing('@url-field');
        $browser->assertMissing('@secret-field');
        $browser->assertMissing('@raw-checkbox');
        $browser->click('@create-button');
    }

    public function editForward(Browser $browser)
    {
        $browser->click('@open-button');
        $browser->click('@edit-button');
        $browser->assertSelected('@type-of-action-field', 'forwarding');
        $browser->assertDisabled('@type-of-action-field');
        $browser->type('@title-field', 'test forward edited');
        $browser->type('@email-field', 'toto-edited@example.com');
        $browser->click('@save-button');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@element' => '#selector',
        ];
    }
}
