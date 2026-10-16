<?php
    namespace App\Services;

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
    }