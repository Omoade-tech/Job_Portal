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
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->string('role'); 
            $table->string('name'); 
            $table->string('email')->unique(); 
            $table->string('password'); 
            $table->string('phoneNumber')->nullable(); 
            $table->string('companyName')->nullable(); 
            $table->integer('age')->nullable(); 
            $table->enum('sex', ['male', 'female', 'other'])->nullable(); 
            $table->enum('status', ['Single', 'Married'])->nullable(); 
            $table->string('address')->nullable(); 
            $table->string('city')->nullable(); 
            $table->string('state')->nullable(); 
            $table->string('country')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};
