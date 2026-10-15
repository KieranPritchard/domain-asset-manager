<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
