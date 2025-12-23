<?php

namespace Iqonic\FileManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Iqonic\FileManager\Services\S3SyncService;
use Iqonic\FileManager\Models\Setting;
use Exception;

class DeletePathFromS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    
    protected $path;

    /**
     * Create a new job instance.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Execute the job.
     */
    public function handle(S3SyncService $s3Service)
    {
        if (!Setting::get('s3_enabled', false)) return;

        $client = $s3Service->getClient();
        $bucket = Setting::get('s3_bucket');

        if (!$client) return;

        try {
            $client->deleteObject([
                'Bucket' => $bucket,
                'Key' => $this->path,
            ]);
        } catch (Exception $e) {
            $this->release(60);
        }
    }
}
