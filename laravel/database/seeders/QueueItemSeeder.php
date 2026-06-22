<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QueueItem;
use App\Models\Playlist;
use App\Models\Track;
use App\Models\User;

class QueueItemSeeder extends Seeder
{
    public function run(): void
    {
        $playlists = Playlist::all();
        $tracks = Track::all();
        $users = User::all();

        // Плейлист 1: "Вечеринка пятницы"
        $playlist1 = $playlists[0];
        $queueItems1 = [
            ['track' => $tracks[0], 'status' => 'playing', 'votes' => 5, 'position' => 1],
            ['track' => $tracks[1], 'status' => 'pending', 'votes' => 3, 'position' => 2],
            ['track' => $tracks[2], 'status' => 'pending', 'votes' => 7, 'position' => 3],
            ['track' => $tracks[3], 'status' => 'pending', 'votes' => 2, 'position' => 4],
            ['track' => $tracks[4], 'status' => 'played', 'votes' => 4, 'position' => 5],
        ];

        foreach ($queueItems1 as $index => $item) {
            QueueItem::create([
                'playlist_id' => $playlist1->id,
                'track_id' => $item['track']->id,
                'added_by' => $users->random()->id,
                'position' => $item['position'],
                'status' => $item['status'],
                'votes_up' => $item['votes'],
                'played_at' => $item['status'] === 'played' ? now()->subMinutes(10) : null,
            ]);
        }

        // Плейлист 2: "Рок-классика"
        $playlist2 = $playlists[1];
        $queueItems2 = [
            ['track' => $tracks[4], 'status' => 'playing', 'votes' => 8, 'position' => 1],
            ['track' => $tracks[5], 'status' => 'pending', 'votes' => 6, 'position' => 2],
            ['track' => $tracks[6], 'status' => 'pending', 'votes' => 4, 'position' => 3],
            ['track' => $tracks[7], 'status' => 'pending', 'votes' => 9, 'position' => 4],
            ['track' => $tracks[8], 'status' => 'skipped', 'votes' => 1, 'position' => 5],
        ];

        foreach ($queueItems2 as $index => $item) {
            QueueItem::create([
                'playlist_id' => $playlist2->id,
                'track_id' => $item['track']->id,
                'added_by' => $users->random()->id,
                'position' => $item['position'],
                'status' => $item['status'],
                'votes_up' => $item['votes'],
                'played_at' => $item['status'] === 'played' ? now()->subMinutes(5) : null,
            ]);
        }

        // Плейлист 3: "Чилл-зона"
        $playlist3 = $playlists[2];
        $queueItems3 = [
            ['track' => $tracks[8], 'status' => 'playing', 'votes' => 3, 'position' => 1],
            ['track' => $tracks[9], 'status' => 'pending', 'votes' => 5, 'position' => 2],
            ['track' => $tracks[2], 'status' => 'pending', 'votes' => 2, 'position' => 3],
            ['track' => $tracks[3], 'status' => 'played', 'votes' => 4, 'position' => 4],
            ['track' => $tracks[0], 'status' => 'pending', 'votes' => 6, 'position' => 5],
        ];

        foreach ($queueItems3 as $index => $item) {
            QueueItem::create([
                'playlist_id' => $playlist3->id,
                'track_id' => $item['track']->id,
                'added_by' => $users->random()->id,
                'position' => $item['position'],
                'status' => $item['status'],
                'votes_up' => $item['votes'],
                'played_at' => $item['status'] === 'played' ? now()->subMinutes(15) : null,
            ]);
        }
    }
}
