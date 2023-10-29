<?php

namespace Tests\Unit;

use App\Automation;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AutomationsTest extends TestCase
{
    use DatabaseMigrations;

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

    /**
     * @test
     */
    public function it_fetches_all_automations()
    {
        $automations = Automation::factory()->count(2)->create();

        $response = $this->json('GET', 'api/automations');

        $response->assertStatus(200);

        $data = $response->getData()->data;
        $this->assertCount(2, $data);
    }

    /**
     * @test
     */
    public function it_can_create_automation()
    {
        $response = $this->json('POST', 'api/automations', [
            'title' => 'My new automation',
            'has_attachments' => false,
            'action_url' => 'https://localhost/webhooks',
            'action_delete_email' => false,
            'post_raw' => false,
        ]);

        $response->assertStatus(200);

        $this->assertCount(1, Automation::all());
    }

    /**
     * @test
     */
    public function it_can_delete_automation()
    {
        $automation = Automation::factory()->create();

        $this->assertCount(1, Automation::all());

        $response = $this->json('DELETE', 'api/automations/'.$automation->id);

        $response->assertStatus(200);

        $this->assertCount(0, Automation::all());
    }

    /**
     * @test
     */
    public function it_can_update_automation()
    {
        $automation = Automation::factory()->create();

        $response = $this->json('PUT', 'api/automations/'.$automation->id, [
            'title' => 'My title has changed',
            'has_attachments' => true,
            'action_url' => 'https://localhost/webhooks-changed',
            'action_delete_email' => false,
            'post_raw' => false,
        ]);

        $response->assertStatus(200);

        $this->assertCount(1, Automation::all());
        $this->assertEquals('My title has changed', Automation::first()->title);
        $this->assertTrue(Automation::first()->has_attachments);
        $this->assertEquals('https://localhost/webhooks-changed', Automation::first()->action_url);
    }
}
