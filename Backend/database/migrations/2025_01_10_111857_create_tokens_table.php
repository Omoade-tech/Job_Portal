<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokensTable extends Migration
{
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_seeker_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tokens');
    }
}
