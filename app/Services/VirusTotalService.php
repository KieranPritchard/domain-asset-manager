<?php
    namespace App\Services;

    use App\Models\VirustotalScan;
    use Illuminate\Http\Client\RequestException;
    use Illuminate\Http\Client\PendingRequest;
    use Illuminate\Support\Facades\Http;

    // Virus total service class
    class VirusTotalService
    {
        // Constants for the service
        private const BASE = "https://www.virustotal.com/api/v3/";
        private const LARGE_FILE = 32 * 1024 * 1024;
        private const PER_MINUTE = 4;

        // Method to send the pending request
        private function http():PendingRequest
        {
            return Http::withHeaders(['x-apikey' => config('services.virustotal.key')])
            ->acceptJson();
        }

        // Method to submit queued files
        public function submitQueued(): void
        {
            // Gets the sent scans so far
            $sent = VirustotalScan::whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subMinute())
            ->count();

            // Finds out how many slots are available
            $slots = max(0, self::PER_MINUTE - $sent);
            if ($slots === 0) {
                return;
            }

            // Gets the queued files
            $queued = VirustotalScan::where('status', 'queued')
                ->orderBy('created_at')
                ->limit($slots)
                ->get();

            // Loops over each of the queued scans
            foreach ($queued as $scan) {
                // Handles the requests in an error handling loop
                try {
                    // Stores the current endpoint
                    $endpoint = self::BASE . 'files';

                    // Checks if the file size is large and creates a large endpoint
                    if ($scan->file_size > self::LARGE_FILE) {
                        $endpoint = $this->http()
                            ->get(self::BASE . 'files/upload_url')
                            ->throw()
                            ->json('data');
                    }

                    // Sends a file over the request
                    $response = $this->http()
                        ->attach('file', fopen($scan->file_path, 'r'), $scan->original_filename)
                        ->post($endpoint)
                        ->throw();

                    // Updates the scaned files
                    $scan->update([
                        'status'       => 'pending',
                        'analysis_id'  => $response->json('data.id'),
                        'submitted_at' => now(),
                    ]);
                } catch (RequestException $e) {
                    report($e);
                    $scan->update(['status' => 'error']);
                }
            }
        }
    }