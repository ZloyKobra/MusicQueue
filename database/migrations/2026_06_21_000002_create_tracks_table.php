<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist');
            $table->unsignedInteger('duration')->nullable(); // в секундах
            $table->string('youtube_url', 500)->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['artist', 'title']);
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
