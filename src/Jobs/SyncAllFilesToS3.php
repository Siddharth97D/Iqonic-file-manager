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

class SyncAllFilesToS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour for large syncs
    
    /**
     * Execute the job.
     */
    public function handle(S3SyncService $s3Service)
    {
        File::where('type', 'file')
            ->whereNull('deleted_at')
            ->chunk(100, function ($files) {
                foreach ($files as $file) {
                    dispatch(new SyncFileToS3($file));
                }
            });
    }
}
