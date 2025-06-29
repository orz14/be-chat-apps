<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use ObjectId\ObjectId;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Users
        $user1 = DB::table('users')->insertGetId([
            'name' => 'Oriz Wahyu N',
            'username' => 'orz14',
            'email' => 'oriezt.id@gmail.com'
        ]);
        $user2 = DB::table('users')->insertGetId([
            'name' => 'Lucky Alma Aficionado Rigel',
            'username' => 'lucky',
            'email' => 'luckyrigel9802@gmail.com'
        ]);
        $user3 = DB::table('users')->insertGetId([
            'name' => 'Penjahat',
            'username' => 'penjahat',
            'email' => 'penjahat@mail.com'
        ]);
        $user4 = DB::table('users')->insertGetId([
            'name' => 'Second Account',
            'username' => 'orz2',
            'email' => 'oriezt5758@gmail.com'
        ]);
        $user5 = DB::table('users')->insertGetId([
            'name' => 'Kodim',
            'username' => 'kodim',
            'email' => 'adhymasfs@gmail.com'
        ]);

        // Rooms
        $personalRoom1 = ObjectId::generate();
        $personalRoom2 = ObjectId::generate();
        $personalRoom3 = ObjectId::generate();
        $personalRoom4 = ObjectId::generate();
        $groupRoom = ObjectId::generate();

        // Personal Room 1
        DB::table('rooms')->insertGetId([
            'id' => $personalRoom1,
            'type' => 'personal'
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $personalRoom1,
            'user_id' => $user1
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $personalRoom1,
            'user_id' => $user2
        ]);

        // Personal Room 2
        DB::table('rooms')->insertGetId([
            'id' => $personalRoom2,
            'type' => 'personal'
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $personalRoom2,
            'user_id' => $user1
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $personalRoom2,
            'user_id' => $user3
        ]);

        // Personal Room 3
        DB::table('rooms')->insertGetId([
            'id' => $personalRoom3,
            'type' => 'personal'
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $personalRoom3,
            'user_id' => $user1
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $personalRoom3,
            'user_id' => $user4
        ]);

        // Personal Room 4
        DB::table('rooms')->insertGetId([
            'id' => $personalRoom4,
            'type' => 'personal'
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $personalRoom4,
            'user_id' => $user1
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $personalRoom4,
            'user_id' => $user5
        ]);

        // Group Room
        DB::table('rooms')->insertGetId([
            'id' => $groupRoom,
            'type' => 'group'
        ]);
        DB::table('room_details')->insert([
            'room_id' => $groupRoom,
            'owner_id' => $user1,
            'name' => 'Sang Penguasa'
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $groupRoom,
            'user_id' => $user1
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $groupRoom,
            'user_id' => $user2
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $groupRoom,
            'user_id' => $user3
        ]);
        DB::table('chat_rooms')->insert([
            'room_id' => $groupRoom,
            'user_id' => $user5
        ]);
    }
}
