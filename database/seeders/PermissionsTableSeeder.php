<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('permissions')->insert([
            ['name' => 'user', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'role', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'event', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'tiket', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'peserta', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
