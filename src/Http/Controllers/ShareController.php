<?php

namespace Iqonic\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Iqonic\FileManager\Facades\FileManager;
use Iqonic\FileManager\Models\File;
use Iqonic\FileManager\Models\FileShare;

class ShareController extends Controller
{
    public function store(Request $request, File $file)
    {
        $this->authorize('share', $file);

        $share = FileManager::createShare($file, $request->all());

        return response()->json($share);
    }

    public function show($token)
    {
        $share = FileShare::where('token', $token)->firstOrFail();

        if ($share->expires_at && $share->expires_at->isPast()) {
            abort(404, 'Link expired');
        }

        if ($share->max_downloads && $share->downloads_count >= $share->max_downloads) {
            abort(404, 'Limit reached');
        }

        // Logic for password check if needed
        // ...

        $share->increment('downloads_count');

        if ($share->file->s3_sync_status === 'synced') {
            $url = app(\Iqonic\FileManager\Services\S3SyncService::class)->getPresignedUrl($share->file);
            if ($url) return redirect()->away($url);
        }

        return response()->download(
            \Storage::disk($share->file->disk)->path($share->file->path)
        );
    }
}
