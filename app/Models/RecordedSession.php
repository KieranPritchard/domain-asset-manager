<?php

namespace App\Models;

use Database\Factories\RecordedSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecordedSession extends Model
{
    /** @use HasFactory<RecordedSessionFactory> */
    use HasFactory;

    // Stores the name of the table the model uses
    protected $table = 'recorded_sessions';

    protected $fillable = [
        'host',
        'file_count',
    ];

    // Method to get the scans
    public function scans(): HasMany
    {
        return $this->hasMany(VirustotalScan::class, 'session_id');
    }
}
