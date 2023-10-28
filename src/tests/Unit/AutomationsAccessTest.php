<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use \Carbon\Carbon;
use App\Automation;
use App\User;

class AutomationsAccessTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_cannot_use_automations_without_feature_flag()
    {
        config([
            'mailcare.auth' => true,
            'mailcare.automations' => false
        ]);
        $automation = Automation::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->json('GET', 'api/automations')->assertStatus(403);
        $this->actingAs($user)->json('POST', 'api/automations')->assertStatus(403);
        $this->actingAs($user)->json('PUT', 'api/automations/'.$automation->id)->assertStatus(403);
        $this->actingAs($user)->json('DELETE', 'api/automations/'.$automation->id)->assertStatus(403);
    }

    /**
     * @test
     */
    public function it_cannot_use_automations_without_authentication_and_feature_flag()
    {
        config([
            'mailcare.auth' => false,
            'mailcare.automations' => false
        ]);
        $automation = Automation::factory()->create();

        $this->json('GET', 'api/automations')->assertStatus(403);
        $this->json('POST', 'api/automations')->assertStatus(403);
        $this->json('PUT', 'api/automations/'.$automation->id)->assertStatus(403);
        $this->json('DELETE', 'api/automations/'.$automation->id)->assertStatus(403);
    }
}