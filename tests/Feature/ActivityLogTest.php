<?php

namespace Iqonic\FileManager\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Iqonic\FileManager\Tests\TestCase;
use Iqonic\FileManager\Events\FileUploaded;
use Iqonic\FileManager\Models\FileActivityLog;

class ActivityLogTest extends TestCase
{
    /** @test */
    public function it_logs_upload_activity()
    {
        Storage::fake('public');
        Event::fake([FileUploaded::class]);

        $file = UploadedFile::fake()->image('document.jpg');

        $this->postJson(config('file-manager.route_prefix') . '/api/files/upload', [
            'file' => $file,
        ]);

        Event::assertDispatched(FileUploaded::class);
        
        // Note: Since we faked events, the listener won't actually run unless we manually trigger it 
        // or don't fake events. For this test, let's verify the event dispatch.
        // To verify DB log, we'd need to NOT fake events, but then we need the listener registered.
    }

    /** @test */
    public function it_creates_db_log_on_upload()
    {
        Storage::fake('public');
        // Don't fake events here so the listener runs

        $file = UploadedFile::fake()->image('document.jpg');

        $this->postJson(config('file-manager.route_prefix') . '/api/files/upload', [
            'file' => $file,
        ]);

        $this->assertDatabaseHas('file_activity_logs', [
            'action' => 'uploaded',
        ]);
    }
}
