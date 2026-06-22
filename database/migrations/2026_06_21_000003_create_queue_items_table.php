<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->onDelete('cascade');
            $table->foreignId('track_id')->constrained()->onDelete('cascade');
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('position')->default(0);
            $table->enum('status', ['pending', 'playing', 'played', 'skipped'])->default('pending');
            $table->unsignedInteger('votes_up')->default(0);
            $table->timestamp('played_at')->nullable();
            $table->timestamps();

            // Составные индексы для основных запросов
            $table->index(['playlist_id', 'status']);
            $table->index(['playlist_id', 'position']);
            $table->index(['playlist_id', 'votes_up']);
            $table->index('added_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_items');
    }
};
