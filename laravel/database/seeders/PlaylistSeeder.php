<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Playlist;
use App\Models\User;

class PlaylistSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = User::where('email', 'organizer@example.com')->first();

        $playlists = [
            [
                'user_id' => $organizer->id,
                'title' => 'Вечеринка пятницы',
                'slug' => 'friday-party',
                'description' => 'Лучшие хиты для пятничной вечеринки',
                'is_public' => true,
            ],
            [
                'user_id' => $organizer->id,
                'title' => 'Рок-классика',
                'slug' => 'rock-classics',
                'description' => 'Легендарные рок-композиции',
                'is_public' => true,
            ],
            [
                'user_id' => $organizer->id,
                'title' => 'Чилл-зона',
                'slug' => 'chill-zone',
                'description' => 'Спокойная музыка для отдыха',
                'is_public' => true,
            ],
        ];

        foreach ($playlists as $playlist) {
            Playlist::create($playlist);
        }
    }
}
