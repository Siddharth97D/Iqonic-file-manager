<?php

namespace Iqonic\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Iqonic\FileManager\Facades\FileManager;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . (config('file-manager.upload.max_size_mb', 100) * 1024),
                'mimetypes:' . implode(',', config('file-manager.upload.allowed_mimes', ['image/jpeg', 'image/png'])),
            ],
        ]);

        try {
            $file = FileManager::upload($request->file('file'), $request->all());
            return response()->json($file, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }


}
