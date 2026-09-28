<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@webgis-banjir.test'],
            [
                'name'              => 'Admin WebGIS',
                'email'             => 'admin@webgis-banjir.test',
                'password'          => Hash::make('admin123'),
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        $this->command->info('✅ Admin user siap: admin@webgis-banjir.test / admin123');
    }
}
