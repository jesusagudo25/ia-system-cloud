<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('reports')->insert([
            [
                'user_id' => 1,
                'title' => 'Monthly Service Report',
                'description' => 'By IAJ ROBOT',
                'filters' => json_encode([
                    'date' => ['month', 'year'],
                    'type_of_person' => ['Interpreter', 'Coordinator', 'All'],
                    'interpreter' => ['Name','Language'],
                    'coordinator' => ['Name', ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'title' => 'Annual Service Report',
                'description' => 'By IAJ ROBOT',
                'filters' => json_encode([
                    'date' => ['year'],
                    'type_of_person' => ['Interpreter', 'Coordinator', 'All'],
                    'interpreter' => ['Name','Language'],
                    'coordinator' => ['Name', ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'title' => 'Cumulative Service Report by Year',
                'description' => 'By IAJ ROBOT',
                'filters' => json_encode([
                    'date' => ['year'],
                    'type_of_person' => ['Interpreter', 'Coordinator', 'All'],
                    'interpreter' => ['Name', 'Language'],
                    'coordinator' => ['Name' ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'title' => 'Monthly Service Report by Payment Period',
                'description' => 'By IAJ ROBOT',
                'filters' => json_encode([
                    'date' => ['month', 'year'],
                    'type_of_person' => ['Interpreter', 'Coordinator', 'All'],
                    'interpreter' => ['Name','Language'],
                    'coordinator' => ['Name', ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
