<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    public function testWebWithoutMailCareAuth()
    {
        $response = $this->get('/');

        $this->assertEquals(false, config('mailcare.auth'));
        $response->assertStatus(200);
    }

    public function testWebWithMailCareAuth()
    {
        config(['mailcare.auth' => true]);
        $user = User::factory()->create();

        $response = $this->get('/');

        $this->assertEquals(true, config('mailcare.auth'));
        $response->assertStatus(401);

        $response = $this->actingAs($user)->get('/');
        $this->assertEquals(true, config('mailcare.auth'));
        $response->assertStatus(200);
    }

    public function testApiWithoutMailCareAuth()
    {
        $response = $this->json('GET', 'api/emails');

        $this->assertEquals(false, config('mailcare.auth'));
        $response->assertStatus(200);
    }

    public function testApiWithMailCareAuth()
    {
        config(['mailcare.auth' => true]);
        $user = User::factory()->create();

        $response = $this->json('GET', 'api/emails');

        $this->assertEquals(true, config('mailcare.auth'));
        $response->assertStatus(401);


        $response = $this->actingAs($user)->json('GET', 'api/emails');
        $this->assertEquals(true, config('mailcare.auth'));
        $response->assertStatus(200);
    }
}
