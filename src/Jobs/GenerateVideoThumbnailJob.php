<?php

namespace Iqonic\FileManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Iqonic\FileManager\Models\File;

class GenerateVideoThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes

    public function __construct(public File $file)
    {
    }

    public function handle(): void
    {
        set_time_limit(0); // Unlimited execution time
        $disk = \Illuminate\Support\Facades\Storage::disk($this->file->disk);

        if (!$disk->exists($this->file->path)) {
            return;
        }

        // Create a local temp file
        $localPath = sys_get_temp_dir() . '/' . basename($this->file->path);
        file_put_contents($localPath, $disk->get($this->file->path));

        try {
            $ffmpeg = \FFMpeg\FFMpeg::create([
                'ffmpeg.binaries'  => config('file-manager.ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => config('file-manager.ffmpeg.ffprobe_path', '/usr/bin/ffprobe'),
                'timeout'          => 3600, // The underlying process time-out
                'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
            ]);
            $video = $ffmpeg->open($localPath);
            
            // Extract frame at 1 second mark
            $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1));
            
            $thumbName = 'thumb_' . pathinfo($this->file->basename, PATHINFO_FILENAME) . '.jpg';
            $thumbLocalPath = sys_get_temp_dir() . '/' . $thumbName;
            
            $frame->save($thumbLocalPath);
            
            // Upload thumbnail to same directory as video
            $thumbPath = dirname($this->file->path) . '/' . $thumbName;
            $disk->put($thumbPath, file_get_contents($thumbLocalPath));
            
            // Update file record
            $this->file->update(['thumbnail_path' => $thumbPath]);

        } finally {
            // Cleanup
            @unlink($localPath);
            if (isset($thumbLocalPath)) {
                @unlink($thumbLocalPath);
            }
        }
    }
}
