<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirustotalScan extends Model
{
    /** @use HasFactory<\Database\Factories\VirustotalScanFactory> */
    use HasFactory;

    // Stores the table to be used
    protected $table = 'virustotal_scans';

    protected $guarded = []; // Allows mass-assignment for all attributes

    // Converts these columns to useful PHP types when reading and writing
    protected $casts = [
        'submitted_at' => 'datetime',
        'raw_response' => 'array',
    ];

    // Relationship: A scan belongs to a recorded session
    public function session(): BelongsTo
    {
        return $this->belongsTo(RecordedSession::class, 'session_id');
    }
}