<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(1)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        DB::table('lenguages')->insert([
            'id' => 1,
            'name' => 'American Sign Language',
            'price_per_hour' => 50,
        ]);

        DB::table('lenguages')->insert([
            'id' => 2,
            'name' => 'Spanish',
            'price_per_hour' => 40,
        ]);

        DB::table('lenguages')->insert([
            'id' => 3,
            'name' => 'Spanish Certified',
            'price_per_hour' => 40,
        ]);

        DB::table('descriptions')->insert([
            'title' => 'Completed'
        ]);

        DB::table('descriptions')->insert([
            'title' => 'Lore cancelation'
        ]);

        DB::table('descriptions')->insert([
            'title' => 'Cancelled upon arrival'
        ]);

        DB::table('descriptions')->insert([
            'title' => 'No show'
        ]);

        DB::table('interpreters')->insert([
            'lenguage_id' => 1,
            'full_name' => 'Orlando perez',
            'ssn' => '33333222',
            'email' => 'test1@test.com',
            'phone_number' => '32232323',
            'address' => '123 Test St',
            'city' => 'Miami',
            'state' => 'Florida',
            'zip_code' => '12345',
        ]);

        DB::table('interpreters')->insert([
            'lenguage_id' => 2,
            'full_name' => 'Test Interpreter',
            'ssn' => '3534523',
            'email' => 'test2@test.com',
            'phone_number' => '4434232',
            'address' => '4332 Test St',
            'city' => 'Miami',
            'state' => 'Florida',
            'zip_code' => '12345',
        ]);

        DB::table('interpreters')->insert([
            'lenguage_id' =>3,
            'full_name' => 'Test Interpreter',
            'ssn' => '123456789',
            'email' => 'test3@test.com',
            'phone_number' => '123456720',
            'address' => '34434 Test St',
            'city' => 'Miami',
            'state' => 'Florida',
            'zip_code' => '12345',
        ]);

        DB::table('coordinators')->insert([
            'full_name' => 'Test Coordinator',
            'ssn' => '123456789',
            'email' => 'coord@email.com',
            'phone_number' => '1233230',
            'address' => '123 Test St',
            'city' => 'Miami',
            'state' => 'Florida',
            'zip_code' => '12345',
        ]);

        DB::table('agencies')->insert([
            'name' => 'Test Agency',
            'email' => 'test@agency.com',
            'phone_number' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Miami',
            'state' => 'Florida',
            'zip_code' => '12345',
        ]);
    }
}
