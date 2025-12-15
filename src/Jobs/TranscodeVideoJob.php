<?php

namespace Iqonic\FileManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Iqonic\FileManager\Models\File;
use Iqonic\FileManager\Events\FileTranscoded;

class TranscodeVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour

    public function __construct(public File $file, public string $profile)
    {
    }

    public function handle(): void
    {
        set_time_limit(0); // Unlimited execution time
        $profiles = config('file-manager.compression.video_profiles');
        
        if (!isset($profiles[$this->profile])) {
            return;
        }

        $config = $profiles[$this->profile];
        $disk = \Illuminate\Support\Facades\Storage::disk($this->file->disk);
        
        if (!$disk->exists($this->file->path)) {
            return;
        }

        // We need a local path for FFmpeg
        $localPath = sys_get_temp_dir() . '/' . basename($this->file->path);
        file_put_contents($localPath, $disk->get($this->file->path));

        $ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => config('file-manager.ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => config('file-manager.ffmpeg.ffprobe_path', '/usr/bin/ffprobe'),
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ]);
        $video = $ffmpeg->open($localPath);

        // Resolution parsing
        [$width, $height] = explode('x', $config['resolution']);
        
        $video->filters()->resize(new \FFMpeg\Coordinate\Dimension($width, $height));
        
        // Transcode
        $format = new \FFMpeg\Format\Video\X264();
        $format->setKiloBitrate((int) filter_var($config['bitrate'], FILTER_SANITIZE_NUMBER_INT));
        
        $newFileName = pathinfo($this->file->basename, PATHINFO_FILENAME) . '_' . $this->profile . '.mp4';
        $newLocalPath = sys_get_temp_dir() . '/' . $newFileName;
        
        $video->save($format, $newLocalPath);
        
        // Store Result
        $newPath = dirname($this->file->path) . '/' . $newFileName;
        $disk->put($newPath, file_get_contents($newLocalPath));
        
        // Create Variant
        $this->file->variants()->create([
            'profile' => $this->profile,
            'disk' => $this->file->disk,
            'path' => $newPath,
            'size' => filesize($newLocalPath),
            'mime_type' => 'video/mp4',
        ]);

        // Generate Thumbnail (Poster) if not exists
        $thumbName = 'poster_' . pathinfo($this->file->basename, PATHINFO_FILENAME) . '.jpg';
        $thumbPath = dirname($this->file->path) . '/' . $thumbName;
        
        if (!$disk->exists($thumbPath)) {
             $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
                   ->save(sys_get_temp_dir() . '/' . $thumbName);
             $disk->put($thumbPath, file_get_contents(sys_get_temp_dir() . '/' . $thumbName));
        }

        // Cleanup
        @unlink($localPath);
        @unlink($newLocalPath);
        @unlink(sys_get_temp_dir() . '/' . $thumbName);

        event(new FileTranscoded($this->file));
    }
}
