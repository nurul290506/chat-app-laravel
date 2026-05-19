<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Membuat 2 User untuk simulasi chat
        $user1 = User::factory()->create([
            'name' => 'Nurul',
            'email' => 'nurul@gmail.com',
        ]);

        $user2 = User::factory()->create([
            'name' => 'Teman Nurul',
            'email' => 'teman@gmail.com',
        ]);

        // 2. Membuat 1 Room Chat Privat
        $conversation = Conversation::create([
            'is_group' => false
        ]);

        // 3. Memasukkan Nurul dan Teman Nurul ke dalam Room Chat tersebut
        $conversation->users()->attach([$user1->id, $user2->id]);
    }
}