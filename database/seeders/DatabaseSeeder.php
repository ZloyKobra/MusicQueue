<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Playlist;
use App\Models\Track;
use App\Models\QueueItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём организатора
        $organizer = User::create([
            'name' => 'Организатор',
            'email' => 'org@example.com',
            'password' => bcrypt('password'),
        ]);

        // Создаём гостей
        $guest1 = User::create([
            'name' => 'Гость 1',
            'email' => 'guest1@example.com',
            'password' => bcrypt('password'),
        ]);

        $guest2 = User::create([
            'name' => 'Гость 2',
            'email' => 'guest2@example.com',
            'password' => bcrypt('password'),
        ]);

        $guest3 = User::create([
            'name' => 'Гость 3',
            'email' => 'guest3@example.com',
            'password' => bcrypt('password'),
        ]);

        // Создаём плейлист
        $playlist = Playlist::create([
            'user_id' => $organizer->id,
            'title' => 'Вечеринка пятницы',
            'slug' => 'friday-party',
            'description' => 'Лучшие хиты для пятничной вечеринки',
            'is_public' => true,
        ]);

        // Создаём треки
        $tracks = [
            ['title' => 'Never Be Like You', 'artist' => 'Flume', 'track_url' => 'https://vkvideo.ru/video-229725480_456239068'],
            ['title' => 'Midnight City', 'artist' => 'M83', 'track_url' => 'https://vkvideo.ru/video-70555882_456239020'],
            ['title' => 'Say My Name', 'artist' => 'ODESZA', 'track_url' => 'https://vkvideo.ru/video-230014252_456239032'],
            ['title' => 'Sleepless', 'artist' => 'Flume', 'track_url' => 'https://vkvideo.ru/video-69616364_456246485'],
        ];

        foreach ($tracks as $trackData) {
            $track = Track::create($trackData);
            
            QueueItem::create([
                'playlist_id' => $playlist->id,
                'track_id' => $track->id,
                'added_by' => [$guest1, $guest2, $guest3][array_rand([$guest1, $guest2, $guest3])]->id,
                'position' => $track->id,
                'status' => $track->id === 1 ? 'playing' : 'pending',
                'votes_up' => rand(1, 10),
            ]);
        }

        echo "✅ База данных заполнена!\n";
        echo "Организатор: org@example.com / password\n";
        echo "Гости: guest1@example.com / password\n";
    }
}
