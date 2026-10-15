<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VirustotalScan extends Model
{
    /** @use HasFactory<\Database\Factories\VirustotalScanFactory> */
    use HasFactory;

    // Stores the table to be used
    protected $table = 'virustotal_scans';

    protected $guarded = []; // Allows mass-assignment for all attributes

    // Relationship: A scan belongs to a recorded session
    public function session()
    {
        return $this->belongsTo(RecordedSession::class, 'session_id');
    }

    // Method creates a new scan record
    public static function create(array $attributes)
    {
        // Checks if the original filename has not been set
        if (!array_key_exists("original_filename", $attributes)) {
            throw new \Exception("Original filename not provided in create method");
        }

        // Checks if the file hash has not been set
        if (!array_key_exists("file_hash", $attributes)) {
            throw new \Exception("File hash not provided in create method");
        }

        // Checks if the session ID has not been set
        if (!array_key_exists("session_id", $attributes)) {
            throw new \Exception("Session ID not provided in create method");
        }

        // Stores the scan data in variables to add to the database
        $original_filename = trim($attributes["original_filename"]);
        $file_hash = trim($attributes["file_hash"]);
        $session_id = $attributes["session_id"];
        $file_size = $attributes["file_size"] ?? null;
        $file_path = $attributes["file_path"] ?? null;
        $status = $attributes["status"] ?? 'queued';

        try {
            // Adds the data to a database and returns the newly inserted record using PostgreSQL RETURNING *
            $insertedRow = DB::select(
                "INSERT INTO virustotal_scans (original_filename, file_hash, session_id, file_size, file_path, status) VALUES (?, ?, ?, ?, ?, ?) RETURNING *", 
                [$original_filename, $file_hash, $session_id, $file_size, $file_path, $status]
            );

            // Returns the first result (the inserted data) if successful
            return $insertedRow[0] ?? null;

        } catch (\Throwable $error) {
            throw $error;
        }
    }
}
