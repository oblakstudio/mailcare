<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use DatabaseMigrations;

    public function test_console_can_create_user()
    {
        $this->assertCount(0, User::all());

        $this->artisan('mailcare:create-user', [
            'name' => 'vincent',
            'email' => 'vincent@localhost.com',
            'password' => 'azerty',
        ])
            ->expectsOutput('Creating...')
            ->expectsOutput('User vincent created')
            ->assertExitCode(0);

        $this->assertCount(1, User::all());
        $this->assertDatabaseHas('users', [
            'name' => 'vincent',
            'email' => 'vincent@localhost.com',
        ]);
    }
}
