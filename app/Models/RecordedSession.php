<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RecordedSession extends Model
{
    /** @use HasFactory<\Database\Factories\RecordedSessionFactory> */
    use HasFactory;

    // Stores the name of the table the model uses
    protected $table = "recorded_sessions";

    protected $fillable = [
        "host",
        "file_count",
    ];

    // Method to get the scans
    public function get_scans()
    {
        return $this->hasMany(VirustotalScan::class, "session_id");
    }

    // Method to create the a recorded session
    public function create(array $attributes)
    {
        // Checks if the host has not been set
        if (!array_key_exists("host", $attributes)) {
            throw new \Exception("Host not provided in create method");
        }

        // Checks if the file count has not been set
        if (!array_key_exists("file_count", $attributes)) {
            throw new \Exception("File count not provided");
        }

        // Stores the session data in variables to add to the database
        $host = e(trim($attributes["host"]));
        $file_count = e(trim($attributes["file_count"]));

        try {
            // Adds the data to a database
            DB::insert("INSERT INTO recorded_sessions (host, file_count) VALUES (?,?)", [$host, $file_count]);
        } catch (\Throwable $error) {
            throw $error;
        }
    }
}
