<?php

declare(strict_types=1);

namespace Zeus\Laravel\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ZeusDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('users')->where('email', 'admin@zeus.local')->exists()) {
            return;
        }

        $userId = DB::table('users')->insertGetId([
            'name' => 'Administrateur Zeus',
            'email' => 'admin@zeus.local',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tenantId = DB::table('zeus_tenants')->insertGetId([
            'code' => 'HQ',
            'name' => 'Quartier Général',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('zeus_tenant_user')->insert([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
