<?php

namespace App\Http\Controllers;

use App\Models\RecordedSession;
use App\Models\VirustotalScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UploadController extends Controller
{
    // renders the view
    public function index(): View
    {
        return view('upload');
    }

    // Method to handle file upload logic
    public function store(Request $request): JsonResponse
    {
        // Checks if the request does not include any files
        if (! $request->hasFile('files')) {
            return response()->json([
                'status' => 'idle',
                'message' => 'No files provided',
            ]);
        }

        // Stores the data from the request
        $host = $request->ip() ?? '127.0.0.1';
        $files = $request->file('files');
        if (! is_array($files)) {
            $files = [$files];
        }
        $fileCount = count($files);

        // Creates the recorded sessions
        $session = RecordedSession::create([
            'host' => $host,
            'file_count' => $fileCount,
        ]);

        // Stores the session id consistently
        $request->session()->put('recorded_session_id', $session->id);

        // Stores the results
        $results = [];

        // Loops over each of the files
        foreach ($files as $file) {
            // Validates the file.
            if (! $file->isValid()) {
                $results[] = [
                    'name' => $file->getClientOriginalName(),
                    'status' => 'error',
                    'message' => 'Upload failed',
                ];

                continue;
            }

            // Stores the file information
            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $tempPath = $file->getRealPath();

            // Hash the temporary file
            $fileHash = hash_file('sha256', $tempPath);

            // Define custom file storage name
            $storedFilename = $fileHash.'_'.$originalName;

            // Store file in storage/app/private/uploads using local disk
            $storedPath = $file->storeAs('uploads', $storedFilename, 'local');
            $absolutePath = Storage::disk('local')->path($storedPath);

            // Creates a new scan
            $scan = VirustotalScan::create([
                'session_id' => $session->id,
                'original_filename' => $originalName,
                'file_hash' => $fileHash,
                'file_size' => $fileSize,
                'file_path' => $absolutePath,
                'status' => 'queued',
            ]);

            // Stores the results
            $results[] = [
                'name' => $originalName,
                'status' => 'queued',
                'scan_id' => $scan->id,
            ];
        }

        return response()->json($results);
    }
}
