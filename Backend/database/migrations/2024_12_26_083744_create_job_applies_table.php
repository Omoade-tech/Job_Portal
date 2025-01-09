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
        Schema::create('job_applies', function (Blueprint $table) {
            $table->id(); 
            $table->text('coverLetter')->nullable(); 
            $table->string('resume')->nullable(); 
            $table->foreignId('job_portals_id')->nullable()
                  ->constrained('job_portals') 
                  ->onDelete('cascade'); 
            $table->foreignId('job_seekers_id')->nullable()
                  ->constrained('job_seekers') 
                  ->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applies');
    }
};
