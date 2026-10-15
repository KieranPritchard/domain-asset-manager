<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('virustotal_scans', function (Blueprint $table) {
            $table->id(); // Equivalent to auto-incrementing primary key (bigint in PostgreSQL)
            
            // Foreign key to recorded_sessions table 
            // (Ensure your recorded_sessions table is created in a migration with an earlier timestamp)
            $table->foreignId('session_id')
                  ->constrained('recorded_sessions')
                  ->onDelete('cascade');

            $table->string('original_filename', 255);
            $table->char('file_hash', 64);
            $table->string('file_path', 500)->nullable();
            $table->integer('file_size')->nullable();
            $table->string('analysis_id', 255)->nullable();
            
            // Enum status field handled safely for PostgreSQL
            $table->enum('status', ['queued', 'pending', 'completed', 'error'])->default('queued');
            
            $table->integer('malicious')->default(0);
            $table->integer('suspicious')->default(0);
            $table->integer('harmless')->default(0);
            $table->integer('undetected')->default(0);
            
            $table->timestamp('scan_date')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            
            // LONGTEXT maps to text/longText in Laravel which translates well to PostgreSQL TEXT
            $table->text('raw_response')->nullable();
            
            $table->timestamps(); // Adds created_at and updated_at if you want them
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virustotal_scans');
    }
};
