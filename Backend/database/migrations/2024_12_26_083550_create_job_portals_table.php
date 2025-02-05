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
        Schema::create('job_portals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->string('companyLogo')->nullable();
            $table->string('companyName'); 
            $table->string('contract'); 
            $table->string('post'); 
            $table->string('salary')->nullable(); 
            $table->text('description')->nullable();
            $table->string('location');
            $table->text('responsibility')->nullable(); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('job_portals')) {
            Schema::dropIfExists('job_portals');
        }
    }
};
