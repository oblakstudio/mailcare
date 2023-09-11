<?php

namespace Tests\Command;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Email;
use App\Attachment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AutoCleanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function cmd_clean_emails()
    {
        $email = Email::factory()->create();
        $emailDeleted2MonthsAgo = Email::factory()->create([
            'deleted_at' => Carbon::now()->subMonths(2)
        ]);
        $emailDeleted10DaysAgo = Email::factory()->create([
            'deleted_at' => Carbon::now()->subDays(10)
        ]);

        $this->assertTrue($email->exists);
        $this->assertTrue($emailDeleted2MonthsAgo->exists);
        $this->assertTrue($emailDeleted10DaysAgo->exists);
        $this->assertCount(3, Email::withTrashed()->get());

        $this->artisan('mailcare:clean')->assertExitCode(0);

        $this->assertTrue($email->fresh()->exists);
        $this->assertNull($emailDeleted2MonthsAgo->fresh());
        $this->assertTrue($emailDeleted10DaysAgo->fresh()->exists);
        $this->assertCount(2, Email::withTrashed()->get());
    }

    /**
     * @test
     */
    public function cmd_clean_attachments_in_database()
    {
        $this->artisan(
            'mailcare:email-receive',
            ['file' => 'tests/storage/email_with_attachment.eml']
        )->assertExitCode(0);

        $email = Email::first();
        $email->deleted_at = Carbon::now()->subMonths(2);
        $email->save();

        $this->assertCount(1, Email::withTrashed()->get());
        $this->assertCount(1, Attachment::all());

        $this->artisan('mailcare:clean')->assertExitCode(0);

        $this->assertNull($email->fresh());
        $this->assertCount(0, Email::withTrashed()->get());
        $this->assertCount(0, Attachment::all());
    }

    /**
     * @test
     */
    public function cmd_clean_files()
    {
        $this->artisan(
            'mailcare:email-receive',
            ['file' => 'tests/storage/email_with_attachment.eml']
        )->assertExitCode(0);

        $email = Email::first();
        $email->deleted_at = Carbon::now()->subMonths(2);
        $email->save();

        $this->assertCount(1, Email::withTrashed()->get());
        $this->assertTrue(Storage::exists($email->path()));

        $this->artisan('mailcare:clean')->assertExitCode(0);

        $this->assertNull($email->fresh());
        $this->assertCount(0, Email::withTrashed()->get());
        $this->assertFalse(Storage::exists($email->path()));
    }
}
