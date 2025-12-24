<?php

namespace Iqonic\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Iqonic\FileManager\Models\Setting;
use Iqonic\FileManager\Services\S3SyncService;
use Iqonic\FileManager\Jobs\SyncAllFilesToS3;
use Aws\S3\S3Client;

class SettingsController extends Controller
{
    public function index()
    {
        $s3Key = Setting::get('s3_key', '');
        $s3Secret = Setting::get('s3_secret', '');

        try { $s3Key = decrypt($s3Key); } catch (\Exception $e) {}
        try { $s3Secret = decrypt($s3Secret); } catch (\Exception $e) {}

        $settings = [
            'compress_images' => Setting::get('compress_images', false),
            'compression_quality' => Setting::get('compression_quality', 80),
            'convert_to_webp' => Setting::get('convert_to_webp', false),
            's3_enabled' => Setting::get('s3_enabled', false),
            's3_key' => $s3Key,
            's3_secret' => $s3Secret,
            's3_region' => Setting::get('s3_region', 'us-east-1'),
            's3_bucket' => Setting::get('s3_bucket', ''),
            's3_root_folder' => Setting::get('s3_root_folder', ''),
            's3_endpoint' => Setting::get('s3_endpoint', ''),
            // Theme settings
            'theme_primary_color' => Setting::get('theme_primary_color', '#3B82F6'),
            'theme_sidebar_bg' => Setting::get('theme_sidebar_bg', '#1F2937'),
            'theme_sidebar_text' => Setting::get('theme_sidebar_text', '#F3F4F6'),
            'theme_sidebar_active' => Setting::get('theme_sidebar_active', '#3B82F6'),
            'theme_sidebar_hover_bg' => Setting::get('theme_sidebar_hover_bg', '#ffffff1a'), 
            'theme_sidebar_hover_text' => Setting::get('theme_sidebar_hover_text', '#ffffff'),
            'theme_active_font_color' => Setting::get('theme_active_font_color', '#ffffff'),
            'theme_border_radius' => Setting::get('theme_border_radius', '0.5rem'),
            'theme_spacing' => Setting::get('theme_spacing', '1rem'),
            'theme_font_family' => Setting::get('theme_font_family', 'Maven Pro, sans-serif'), 
            'theme_font_size' => Setting::get('theme_font_size', '14px'),
            // Video settings
            'video_thumbnails_enabled' => Setting::get('video_thumbnails_enabled', true),
            'ffmpeg_path' => Setting::get('ffmpeg_path', config('file-manager.ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg')),
            'ffprobe_path' => Setting::get('ffprobe_path', config('file-manager.ffmpeg.ffprobe_path', '/usr/bin/ffprobe')),
        ];
        
        $targetInput = ''; // Required by layout
        
        return view('file-manager::settings', compact('settings', 'targetInput'));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'compress_images' => 'boolean',
            'compression_quality' => 'integer|min:1|max:100',
            'convert_to_webp' => 'boolean',
            's3_enabled' => 'boolean',
            's3_key' => 'nullable|string',
            's3_secret' => 'nullable|string',
            's3_region' => 'nullable|string',
            's3_bucket' => 'nullable|string',
            's3_root_folder' => 'nullable|string',
            's3_endpoint' => 'nullable|string',
            // Theme validation
            'theme_primary_color' => 'nullable|string',
            'theme_sidebar_bg' => 'nullable|string',
            'theme_sidebar_text' => 'nullable|string',
            'theme_sidebar_active' => 'nullable|string',
            'theme_sidebar_hover_bg' => 'nullable|string',
            'theme_sidebar_hover_text' => 'nullable|string',
            'theme_active_font_color' => 'nullable|string',
            'theme_border_radius' => 'nullable|string',
            'theme_spacing' => 'nullable|string',
            'theme_font_family' => 'nullable|string',
            'theme_font_size' => 'nullable|string',
            // Video validation
            'video_thumbnails_enabled' => 'boolean',
            'ffmpeg_path' => 'nullable|string',
            'ffprobe_path' => 'nullable|string',
        ]);
        
        foreach ($validated as $key => $value) {
            // Encrypt sensitive S3 keys
            if ($key === 's3_key' || $key === 's3_secret') {
                if ($value) {
                    $value = encrypt($value);
                }
            }
            Setting::set($key, $value);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully'
        ]);
    }
    
    public function testS3Connection(Request $request)
    {
        $validated = $request->validate([
            's3_key' => 'required|string',
            's3_secret' => 'required|string',
            's3_region' => 'required|string',
            's3_bucket' => 'required|string',
        ]);
        
        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $validated['s3_region'],
                'credentials' => [
                    'key' => $validated['s3_key'],
                    'secret' => $validated['s3_secret'],
                ],
            ]);
            
            // Test bucket access
            $result = $s3Client->headBucket(['Bucket' => $validated['s3_bucket']]);
            
            return response()->json([
                'success' => true,
                'message' => 'Connected successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function syncExistingData()
    {
        if (!Setting::get('s3_enabled', false)) {
            return response()->json(['success' => false, 'message' => 'S3 is not enabled.'], 422);
        }

        dispatch(new SyncAllFilesToS3());

        return response()->json([
            'success' => true,
            'message' => 'Bulk sync started in the background.'
        ]);
    }



    public function testFFMpeg(Request $request)
    {
        $validated = $request->validate([
            'ffmpeg_path' => 'required|string',
            'ffprobe_path' => 'required|string',
        ]);

        try {
            $ffmpegPath = $validated['ffmpeg_path'];
            $ffprobePath = $validated['ffprobe_path'];

            $ffmpegOutput = [];
            $ffmpegReturnVar = -1;
            exec(escapeshellcmd($ffmpegPath) . " -version 2>&1", $ffmpegOutput, $ffmpegReturnVar);

            $ffprobeOutput = [];
            $ffprobeReturnVar = -1;
            exec(escapeshellcmd($ffprobePath) . " -version 2>&1", $ffprobeOutput, $ffprobeReturnVar);

            if ($ffmpegReturnVar === 0 && $ffprobeReturnVar === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'FFmpeg and FFProbe are working properly!',
                    'ffmpeg_version' => $ffmpegOutput[0] ?? 'Unknown',
                    'ffprobe_version' => $ffprobeOutput[0] ?? 'Unknown',
                ]);
            } else {
                $error = "FFmpeg test failed. ";
                if ($ffmpegReturnVar !== 0) $error .= "FFmpeg Error (Code $ffmpegReturnVar). ";
                if ($ffprobeReturnVar !== 0) $error .= "FFProbe Error (Code $ffprobeReturnVar). ";
                
                return response()->json([
                    'success' => false,
                    'message' => $error,
                    'output' => implode("\n", array_merge($ffmpegOutput, $ffprobeOutput))
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ], 422);
        }
    }
}
