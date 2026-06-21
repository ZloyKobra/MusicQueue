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
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
                'duration' => 212,
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'cover_url' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/mqdefault.jpg',
            ],
            [
                'title' => 'Bohemian Rhapsody',
                'artist' => 'Queen',
                'duration' => 354,
                'youtube_url' => 'https://www.youtube.com/watch?v=fJ9rUzIMcZQ',
                'cover_url' => 'https://img.youtube.com/vi/fJ9rUzIMcZQ/mqdefault.jpg',
            ],
            [
                'title' => 'Shape of You',
                'artist' => 'Ed Sheeran',
                'duration' => 233,
                'youtube_url' => 'https://www.youtube.com/watch?v=JGwWNGJdvx8',
                'cover_url' => 'https://img.youtube.com/vi/JGwWNGJdvx8/mqdefault.jpg',
            ],
            [
                'title' => 'Blinding Lights',
                'artist' => 'The Weeknd',
                'duration' => 200,
                'youtube_url' => 'https://www.youtube.com/watch?v=4NRXx6U8ABQ',
                'cover_url' => 'https://img.youtube.com/vi/4NRXx6U8ABQ/mqdefault.jpg',
            ],
            [
                'title' => 'Smells Like Teen Spirit',
                'artist' => 'Nirvana',
                'duration' => 301,
                'youtube_url' => 'https://www.youtube.com/watch?v=hTWKbfoikeg',
                'cover_url' => 'https://img.youtube.com/vi/hTWKbfoikeg/mqdefault.jpg',
            ],
            [
                'title' => 'Hotel California',
                'artist' => 'Eagles',
                'duration' => 391,
                'youtube_url' => 'https://www.youtube.com/watch?v=BciS5krYL80',
                'cover_url' => 'https://img.youtube.com/vi/BciS5krYL80/mqdefault.jpg',
            ],
            [
                'title' => 'Sweet Child O\' Mine',
                'artist' => 'Guns N\' Roses',
                'duration' => 356,
                'youtube_url' => 'https://www.youtube.com/watch?v=1w7OgIMMRc4',
                'cover_url' => 'https://img.youtube.com/vi/1w7OgIMMRc4/mqdefault.jpg',
            ],
            [
                'title' => 'Stairway to Heaven',
                'artist' => 'Led Zeppelin',
                'duration' => 482,
                'youtube_url' => 'https://www.youtube.com/watch?v=QkF3oxziUI4',
                'cover_url' => 'https://img.youtube.com/vi/QkF3oxziUI4/mqdefault.jpg',
            ],
            [
                'title' => 'Imagine',
                'artist' => 'John Lennon',
                'duration' => 187,
                'youtube_url' => 'https://www.youtube.com/watch?v=YkgkThdzX-8',
                'cover_url' => 'https://img.youtube.com/vi/YkgkThdzX-8/mqdefault.jpg',
            ],
            [
                'title' => 'Billie Jean',
                'artist' => 'Michael Jackson',
                'duration' => 294,
                'youtube_url' => 'https://www.youtube.com/watch?v=Zi_XLOBDo_Y',
                'cover_url' => 'https://img.youtube.com/vi/Zi_XLOBDo_Y/mqdefault.jpg',
            ],
        ];

        foreach ($tracks as $track) {
            Track::create($track);
        }
    }
}
