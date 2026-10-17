<?php

namespace Tests\Feature;

use App\Models\RecordedSession;
use App\Models\VirustotalScan;
use App\Services\VirusTotalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_to_upload_if_no_session(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/upload');
    }

    public function test_dashboard_renders_with_valid_session(): void
    {
        $session = RecordedSession::factory()->create();
        VirustotalScan::factory()->create([
            'session_id' => $session->id,
            'status' => 'completed',
            'malicious' => 2,
            'suspicious' => 1,
            'harmless' => 10,
            'undetected' => 3,
        ]);

        $response = $this->withSession(['recorded_session_id' => $session->id])
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas('totalFiles', 1);
        $response->assertViewHas('totalMalicous', 2);
        $response->assertViewHas('totalSuspicous', 1);
        $response->assertViewHas('totalHarmless', 10);
        $response->assertViewHas('totalUndetected', 3);
        $response->assertViewHas('complete', true);
    }

    public function test_json_endpoint_returns_session_scans(): void
    {
        $session = RecordedSession::factory()->create();
        $scan = VirustotalScan::factory()->create([
            'session_id' => $session->id,
            'original_filename' => 'virus.exe',
        ]);

        $response = $this->withSession(['recorded_session_id' => $session->id])
            ->getJson('/json');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['original_filename' => 'virus.exe']);
    }

    public function test_status_endpoint_returns_pending_count(): void
    {
        config(['services.virustotal.key' => 'fake-test-key']);

        $session = RecordedSession::factory()->create();
        VirustotalScan::factory()->create([
            'session_id' => $session->id,
            'status' => 'pending',
            'analysis_id' => 'analysis-123',
        ]);

        Http::fake([
            'https://www.virustotal.com/api/v3/analyses/analysis-123' => Http::response([
                'data' => [
                    'attributes' => [
                        'status' => 'completed',
                        'stats' => [
                            'malicious' => 3,
                            'suspicious' => 0,
                            'harmless' => 45,
                            'undetected' => 1,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession(['recorded_session_id' => $session->id])
            ->getJson('/scans/status');

        $response->assertStatus(200);
        $response->assertJson([
            'all_scans_complete' => true,
            'pending' => 0,
        ]);

        $this->assertDatabaseHas('virustotal_scans', [
            'analysis_id' => 'analysis-123',
            'status' => 'completed',
            'malicious' => 3,
        ]);
    }

    public function test_virustotal_service_submits_queued_scans(): void
    {
        Storage::fake('local');
        $filePath = Storage::disk('local')->path('uploads/test_file.bin');
        if (! is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        file_put_contents($filePath, 'malicious payload content');

        $session = RecordedSession::factory()->create();
        $scan = VirustotalScan::factory()->create([
            'session_id' => $session->id,
            'status' => 'queued',
            'file_path' => $filePath,
            'file_size' => filesize($filePath),
            'original_filename' => 'test_file.bin',
        ]);

        Http::fake([
            'https://www.virustotal.com/api/v3/files' => Http::response([
                'data' => [
                    'id' => 'generated-analysis-id-456',
                ],
            ], 200),
        ]);

        config(['services.virustotal.key' => 'fake-test-key']);

        $service = app(VirusTotalService::class);
        $service->submitQueued();

        $scan->refresh();
        $this->assertEquals('pending', $scan->status);
        $this->assertEquals('generated-analysis-id-456', $scan->analysis_id);
        $this->assertNotNull($scan->submitted_at);
    }
}
