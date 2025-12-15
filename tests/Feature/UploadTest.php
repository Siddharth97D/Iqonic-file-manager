<?php

namespace Iqonic\FileManager\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Iqonic\FileManager\Tests\TestCase;
use Iqonic\FileManager\Models\File;

class UploadTest extends TestCase
{
    /** @test */
    public function it_can_upload_a_file()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson(config('file-manager.route_prefix') . '/api/files/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('files', [
            'basename' => 'avatar.jpg',
        ]);

        Storage::disk('public')->assertExists($response->json('path'));
    }
}
