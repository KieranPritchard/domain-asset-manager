<?php

namespace App\Services;

use App\Models\VirustotalScan;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

// Virus total service class
class VirusTotalService
{
    // Constants for the service
    private const BASE = 'https://www.virustotal.com/api/v3/';

    private const LARGE_FILE = 32 * 1024 * 1024;

    private const PER_MINUTE = 4;

    // Method to send the pending request
    private function http(): PendingRequest
    {
        return Http::withHeaders(['x-apikey' => config('services.virustotal.key')])
            ->acceptJson();
    }

    // Method to submit queued files
    public function submitQueued(): void
    {
        $apiKey = config('services.virustotal.key');
        if (! $apiKey) {
            Log::warning('VirusTotal API key is not configured.');

            return;
        }

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
            if (! $scan->file_path || ! file_exists($scan->file_path)) {
                Log::error("File not found for scan {$scan->id} at {$scan->file_path}");
                $scan->update(['status' => 'error']);

                continue;
            }

            $handle = fopen($scan->file_path, 'r');
            if (! $handle) {
                Log::error("Unable to open file for scan {$scan->id} at {$scan->file_path}");
                $scan->update(['status' => 'error']);

                continue;
            }

            try {
                // Stores the current endpoint
                $endpoint = self::BASE.'files';

                // Checks if the file size is large and creates a large endpoint
                if ($scan->file_size > self::LARGE_FILE) {
                    $endpoint = $this->http()
                        ->get(self::BASE.'files/upload_url')
                        ->throw()
                        ->json('data');
                }

                // Sends a file over the request
                $response = $this->http()
                    ->attach('file', $handle, $scan->original_filename)
                    ->post($endpoint)
                    ->throw();

                // Updates the scaned files
                $scan->update([
                    'status' => 'pending',
                    'analysis_id' => $response->json('data.id'),
                    'submitted_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
                $scan->update(['status' => 'error']);
            } finally {
                if (is_resource($handle)) {
                    fclose($handle);
                }
            }
        }
    }

    /** Poll pending scans for a session; returns how many are still pending */
    public function refreshPending(int $sessionId): int
    {
        $apiKey = config('services.virustotal.key');
        if (! $apiKey) {
            return VirustotalScan::where('status', 'pending')
                ->where('session_id', $sessionId)
                ->count();
        }

        // Gets the pending files
        $pending = VirustotalScan::where('status', 'pending')
            ->where('session_id', $sessionId)
            ->get();

        // Loops over each pending scan
        foreach ($pending as $scan) {
            if (! $scan->analysis_id) {
                $scan->update(['status' => 'error']);

                continue;
            }

            try {
                // Creates an analysis
                $analysis = $this->http()
                    ->get(self::BASE."analyses/{$scan->analysis_id}")
                    ->throw()
                    ->json();
            } catch (RequestException|ConnectionException) {
                continue; // stays pending, retried next poll
            } catch (Throwable $e) {
                report($e);

                continue;
            }

            $status = data_get($analysis, 'data.attributes.status');
            // Gets the data for analysis and updates it
            if ($status === 'completed') {
                $stats = data_get($analysis, 'data.attributes.stats', []);

                $scan->update([
                    'status' => 'completed',
                    'malicious' => (int) ($stats['malicious'] ?? 0),
                    'suspicious' => (int) ($stats['suspicious'] ?? 0),
                    'harmless' => (int) ($stats['harmless'] ?? 0),
                    'undetected' => (int) ($stats['undetected'] ?? 0),
                    'raw_response' => $analysis,
                ]);
            } elseif (in_array($status, ['failed', 'cancelled'], true)) {
                $scan->update([
                    'status' => 'error',
                    'raw_response' => $analysis,
                ]);
            }
        }

        // returns the pending status
        return VirustotalScan::where('status', 'pending')
            ->where('session_id', $sessionId)
            ->count();
    }
}
