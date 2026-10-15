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
        Schema::create('recorded_session', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key (bigint in PostgreSQL)
            $table->string('host', 255);
            $table->integer('file_count')->default(0);
            
            // Adds created_at and updated_at timestamps automatically.
            // (Laravel's default timestamps function handles CURRENT_TIMESTAMP automatically).
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recorded_session');
    }
};