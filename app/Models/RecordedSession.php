<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
