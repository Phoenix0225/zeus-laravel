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

        DB::table('zeus_business_rules')->insert([
            'entity_code' => 'users',
            'trigger_event' => 'after_create',
            'conditions' => json_encode([]),
            'actions' => json_encode([
                ['class' => 'App\\Zeus\\Actions\\DummyLogAction', 'params' => ['message' => 'Nouvel utilisateur créé']]
            ]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $entityId = DB::table('x_entities')->insertGetId([
            'code' => 'users',
            'name' => 'Utilisateurs',
            'table_name' => 'users',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('x_fields')->insert([
            [
                'entity_id' => $entityId,
                'name' => 'name',
                'column_name' => 'name',
                'type' => 'string',
                'is_required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'entity_id' => $entityId,
                'name' => 'email',
                'column_name' => 'email',
                'type' => 'string',
                'is_required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('x_ui_screens')->insert([
            'code' => 'users_grid',
            'type' => 'grid',
            'entity_code' => 'users',
            'config' => json_encode(['columns' => ['name', 'email']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('x_ui_screens')->insert([
            'code' => 'users_form',
            'type' => 'form',
            'entity_code' => 'users',
            'config' => json_encode([
                'fields' => [
                    ['name' => 'name', 'label' => 'Nom Complet', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Adresse Courriel', 'type' => 'email', 'required' => true]
                ]
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('x_ui_menus')->insert([
            'code' => 'users_menu',
            'label' => 'Gestion Utilisateurs',
            'icon' => 'users',
            'screen_id' => 'users_grid',
            'order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->call(\Zeus\Laravel\Database\Seeders\ZeusStudioSeeder::class);
    }
}
