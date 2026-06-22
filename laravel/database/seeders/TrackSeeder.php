<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Track;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        $tracks = [
            [
                'title' => 'Never Be Like You (feat. Kai)',
                'artist' => 'Flume',
                'duration' => 232,
                'track_url' => 'https://soundcloud.com/flume/never-be-like-you-feat-kai',
                'cover_url' => null,
            ],
            [
                'title' => 'Say My Name (feat. Zyra)',
                'artist' => 'ODESZA',
                'duration' => 258,
                'track_url' => 'https://soundcloud.com/odesza/say-my-name-feat-zyra',
                'cover_url' => null,
            ],
            [
                'title' => 'Midnight City',
                'artist' => 'M83',
                'duration' => 243,
                'track_url' => 'https://soundcloud.com/m83/midnight-city',
                'cover_url' => null,
            ],
            [
                'title' => 'Something Good',
                'artist' => 'ODESZA',
                'duration' => 267,
                'track_url' => 'https://soundcloud.com/odesza/something-good',
                'cover_url' => null,
            ],
            [
                'title' => 'Sleepless',
                'artist' => 'Flume',
                'duration' => 201,
                'track_url' => 'https://soundcloud.com/flume/sleepless',
                'cover_url' => null,
            ],
            [
                'title' => 'Bloom',
                'artist' => 'ODESZA',
                'duration' => 245,
                'track_url' => 'https://soundcloud.com/odesza/bloom',
                'cover_url' => null,
            ],
            [
                'title' => 'Holdin On',
                'artist' => 'Flume',
                'duration' => 238,
                'track_url' => 'https://soundcloud.com/flume/holdin-on',
                'cover_url' => null,
            ],
            [
                'title' => 'Line of Sight (feat. WYNNE & Mansionair)',
                'artist' => 'ODESZA',
                'duration' => 276,
                'track_url' => 'https://soundcloud.com/odesza/line-of-sight',
                'cover_url' => null,
            ],
            [
                'title' => 'On Top (feat. T-Shyne)',
                'artist' => 'Flume',
                'duration' => 195,
                'track_url' => 'https://soundcloud.com/flume/on-top',
                'cover_url' => null,
            ],
            [
                'title' => 'A Moment Apart',
                'artist' => 'ODESZA',
                'duration' => 289,
                'track_url' => 'https://soundcloud.com/odesza/a-moment-apart',
                'cover_url' => null,
            ],
        ];

        foreach ($tracks as $track) {
            Track::create($track);
        }
    }
}
