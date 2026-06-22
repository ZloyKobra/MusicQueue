<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->renameColumn('youtube_url', 'track_url');
            $table->renameColumn('cover_url', 'cover_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->renameColumn('track_url', 'youtube_url');
        });
    }
};
