<?php

namespace Iqonic\FileManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Iqonic\FileManager\Models\File;
use Iqonic\FileManager\Services\S3SyncService;
use Exception;

class SyncFileToS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [60, 300, 600];

    protected $file;

    /**
     * Create a new job instance.
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     */
    public function handle(S3SyncService $s3Service)
    {
        try {
            $s3Service->syncFile($this->file);
        } catch (Exception $e) {
            $this->release(60);
        }
    }
}
