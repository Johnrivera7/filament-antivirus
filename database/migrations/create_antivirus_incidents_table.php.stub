<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antivirus_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('status', 32)->index();
            $table->string('verdict', 32)->index();
            $table->string('signature')->nullable();
            $table->string('original_path', 2048);
            $table->string('quarantine_path', 2048)->nullable();
            $table->string('sha256', 64)->nullable()->index();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('source', 64)->default('manual');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antivirus_incidents');
    }
};
