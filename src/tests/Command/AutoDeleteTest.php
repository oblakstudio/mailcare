<?php

namespace Tests\Command;

use App\Console\Commands\AutoDelete;
use App\Email;
use App\Statistic;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->delete = $this->mock(AutoDelete::class, function ($mock) {
            $mock->shouldReceive('line')->andReturn(null);
            $mock->shouldReceive('comment')->andReturn(null);
            $mock->shouldReceive('info')->andReturn(null);
        })->makePartial();
    }

    private function hasDiskFreeSpace($value)
    {
        $this->delete->shouldReceive(['getDiskFreeSpace' => $value]);
    }

    private function hasCalculatedSize($value)
    {
        $this->delete->shouldReceive(['getCalculatedSize' => $value]);
    }

    private function assertSizeToDeleteEquals($value)
    {
        $this->assertEquals($value, $this->delete->getCalculatedSize());
    }

    /**
     * @test
     */
    public function cmd_delete_only_if_necessary()
    {
        $this->hasCalculatedSize(0);

        Email::factory()->create();

        $this->assertCount(1, Email::all());

        $this->delete->handle();

        $this->assertCount(1, Email::all());
    }

    /**
     * @test
     */
    public function cmd_delete_by_oldest()
    {
        $this->hasCalculatedSize(10);

        $oldestEmail = Email::factory()->create([
            'created_at' => '2017-01-01',
            'size_in_bytes' => 10,
        ]);
        $email = Email::factory()->create([
            'created_at' => '2018-01-01',
            'size_in_bytes' => 20,
        ]);

        $this->assertCount(2, Email::all());
        $this->assertCount(0, Email::onlyTrashed()->get());

        $this->delete->handle();

        $this->assertCount(1, Email::all());
        $this->assertCount(1, Email::onlyTrashed()->get());
        $this->assertTrue($oldestEmail->refresh()->trashed());
        $this->assertFalse($email->refresh()->trashed());
    }

    /**
     * @test
     */
    public function cmd_never_delete_favorite()
    {
        $this->hasCalculatedSize(10);

        $favoriteEmail = Email::factory()->create([
            'created_at' => '2017-01-01',
            'favorite' => true,
            'size_in_bytes' => 10,
        ]);
        $email = Email::factory()->create([
            'created_at' => '2018-01-01',
            'size_in_bytes' => 20,
        ]);

        $this->assertCount(2, Email::all());
        $this->assertCount(0, Email::onlyTrashed()->get());

        $this->delete->handle();

        $this->assertCount(1, Email::all());
        $this->assertCount(1, Email::onlyTrashed()->get());
        $this->assertFalse($favoriteEmail->refresh()->trashed());
        $this->assertTrue($email->refresh()->trashed());
    }

    /**
     * @test
     */
    public function cmd_delete_emails_when_storage_used_increase()
    {
        $this->hasDiskFreeSpace(30);

        Statistic::factory()->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 20,
            'created_at' => Carbon::now()->subDays(40),
        ]);
        Statistic::factory()->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 30,
            'created_at' => Carbon::now()->subDays(15),
        ]);

        $this->assertSizeToDeleteEquals(15);
    }

    /**
     * @test
     */
    public function cmd_delete_emails_when_storage_used_decrease()
    {
        $this->hasDiskFreeSpace(5);

        Statistic::factory()->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 100,
            'created_at' => Carbon::now()->subDays(40),
        ]);
        Statistic::factory()->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 50,
            'created_at' => Carbon::now()->subDays(15),
        ]);

        $this->assertSizeToDeleteEquals(20);
    }

    /**
     * @test
     */
    public function cmd_dont_delete_when_no_data_for_the_first_period()
    {
        $this->hasDiskFreeSpace(10);

        Statistic::factory()->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 10,
            'created_at' => Carbon::now()->subDays(15),
        ]);

        $this->assertSizeToDeleteEquals(0);
    }

    /**
     * @test
     */
    public function cmd_dont_delete_when_no_data_for_the_second_period()
    {
        $this->hasDiskFreeSpace(10);

        Statistic::factory()->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 10,
            'created_at' => Carbon::now()->subDays(40),
        ]);

        $this->assertSizeToDeleteEquals(0);
    }

    /**
     * @test
     */
    public function cmd_dont_delete_when_enough_free_space()
    {
        $this->hasDiskFreeSpace(150);

        Statistic::factory()->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 100,
            'created_at' => Carbon::now()->subDays(40),
        ]);
        Statistic::factory()->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 120,
            'created_at' => Carbon::now()->subDays(15),
        ]);

        $this->assertSizeToDeleteEquals(0);
    }

    /**
     * @test
     */
    public function cmd_should_calculate_the_emails_already_deleted()
    {
        $this->hasDiskFreeSpace(50);

        Statistic::factory()->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 100,
            'created_at' => Carbon::now()->subDays(40),
        ]);
        Statistic::factory()->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 120,
            'created_at' => Carbon::now()->subDays(15),
        ]);

        Email::factory()->create([
            'size_in_bytes' => 80,
            'deleted_at' => Carbon::now()->subDays(10),
        ]);

        $this->assertSizeToDeleteEquals(14);
    }
}
