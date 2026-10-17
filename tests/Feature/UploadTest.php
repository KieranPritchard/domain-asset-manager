<?php

namespace Tests\Feature;

use App\Models\RecordedSession;
use App\Models\VirustotalScan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_page_can_be_rendered(): void
    {
        $response = $this->get('/upload');
        $response->assertStatus(200);
        $response->assertViewIs('upload');
    }

    public function test_root_url_renders_upload_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('upload');
    }

    public function test_upload_returns_idle_when_no_files_sent(): void
    {
        $response = $this->postJson('/upload');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'idle',
            'message' => 'No files provided',
        ]);
    }

    public function test_upload_stores_files_and_records_session(): void
    {
        Storage::fake('local');

        $file1 = UploadedFile::fake()->create('sample1.exe', 100);
        $file2 = UploadedFile::fake()->create('sample2.pdf', 50);

        $response = $this->post('/upload', [
            'files' => [$file1, $file2],
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['name' => 'sample1.exe', 'status' => 'queued']);
        $response->assertJsonFragment(['name' => 'sample2.pdf', 'status' => 'queued']);

        $this->assertDatabaseCount('recorded_sessions', 1);
        $this->assertDatabaseCount('virustotal_scans', 2);

        $session = RecordedSession::first();
        $this->assertEquals(2, $session->file_count);
        $response->assertSessionHas('recorded_session_id', $session->id);

        $scan = VirustotalScan::where('original_filename', 'sample1.exe')->first();
        $this->assertNotNull($scan);
        $this->assertEquals($session->id, $scan->session_id);
        $this->assertEquals('queued', $scan->status);
    }
}
