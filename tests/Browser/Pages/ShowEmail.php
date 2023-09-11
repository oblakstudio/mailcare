<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

class ShowEmail extends BasePage
{
    protected $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function url()
    {
        return "/emails/{$this->email->id}";
    }

    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url());
    }

    public function assertSeeEmail(Browser $browser)
    {
        $browser->waitForText($this->email->subject)
                    ->assertSeeIn('h1', $this->email->subject)
                    ->waitForText($this->email->attachments->count().' attachments');
    }

    public function favorite(Browser $browser)
    {
        $browser->click('@favorite-button');
    }

    public function delete(Browser $browser)
    {
        $browser->click('@delete-button');
    }

    public function elements()
    {
        return [
            '@element' => '#selector',
        ];
    }
}
