<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Организатор',
                'email' => 'organizer@example.com',
                'password' => Hash::make('password'),
                'github_id' => 100001,
                'avatar' => 'https://avatars.githubusercontent.com/u/100001',
            ],
            [
                'name' => 'Гость 1',
                'email' => 'guest1@example.com',
                'password' => Hash::make('password'),
                'github_id' => 100002,
                'avatar' => 'https://avatars.githubusercontent.com/u/100002',
            ],
            [
                'name' => 'Гость 2',
                'email' => 'guest2@example.com',
                'password' => Hash::make('password'),
                'github_id' => 100003,
                'avatar' => 'https://avatars.githubusercontent.com/u/100003',
            ],
            [
                'name' => 'Гость 3',
                'email' => 'guest3@example.com',
                'password' => Hash::make('password'),
                'github_id' => 100004,
                'avatar' => 'https://avatars.githubusercontent.com/u/100004',
            ],
            [
                'name' => 'Гость 4',
                'email' => 'guest4@example.com',
                'password' => Hash::make('password'),
                'github_id' => 100005,
                'avatar' => 'https://avatars.githubusercontent.com/u/100005',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
