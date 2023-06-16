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
            'full_name' => 'Orlando Perez',
            'ssn' => '33333222',
            'email' => 'orlando@interprete.com',
            'phone_number' => '32232323',
            'address' => '2600 NW 5TH AVE',
            'city' => 'Miami',
            'state' => 'Florida',
            'zip_code' => '33182',
        ]);

        DB::table('interpreters')->insert([
            'lenguage_id' => 2,
            'full_name' => 'Jessica Agudo',
            'ssn' => '3534523',
            'email' => 'jessica@interprete.com',
            'phone_number' => '4434232',
            'address' => '8005 NW 8TH ST APT 401',
            'city' => 'Miami',
            'state' => 'Florida',
            'zip_code' => '12345',
        ]);

        DB::table('interpreters')->insert([
            'lenguage_id' =>3,
            'full_name' => 'Carmen Ortega',
            'ssn' => '123456789',
            'email' => 'carmen@interprete.com',
            'phone_number' => '123456720',
            'address' => '1700 NE 198TH TER',
            'city' => 'Miami',
            'state' => 'Florida',
            'zip_code' => '12345',
        ]);

        DB::table('coordinators')->insert([
            'full_name' => 'Mikey Williams',
            'ssn' => '123456789',
            'email' => 'mikey@williams.com',
            'phone_number' => '1233230',
            'address' => '1970 NW 82ND AVE',
            'city' => 'DORAL',
            'state' => 'Florida',
            'zip_code' => '33126-1049',
        ]);

        DB::table('agencies')->insert([
            'name' => 'Lenguage Association',
            'email' => 'lenguage@agency.com',
            'phone_number' => '1234567890',
            'address' => '1970 NW 82ND AVE',
            'city' => 'DORAL',
            'state' => 'Florida',
            'zip_code' => '33126-1049',
        ]);

/*         DB::table('addresses')->insert([
            'address' => '1970 NW 82ND AVE',
            'city' => 'DORAL',
            'state' => 'Florida',
            'state_abbr' => 'FL',
            'zip_code' => '33126-1049',
        ]); */
    }
}
