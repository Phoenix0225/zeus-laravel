<?php

declare(strict_types=1);

namespace Zeus\Laravel\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZeusStudioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Déclarer les Entités Système
        $entitiesId = DB::table('x_entities')->insertGetId([
            'code' => 'system_entities', 'name' => 'Entités (Tables)', 'table_name' => 'x_entities', 'module' => 'core', 'created_at' => now(), 'updated_at' => now()
        ]);
        $fieldsId = DB::table('x_entities')->insertGetId([
            'code' => 'system_fields', 'name' => 'Champs (Colonnes)', 'table_name' => 'x_fields', 'module' => 'core', 'created_at' => now(), 'updated_at' => now()
        ]);

        // 2. Déclarer les Écrans pour les Entités (Grid + Form)
        DB::table('x_ui_screens')->insert([
            ['code' => 'system_entities_grid', 'type' => 'grid', 'entity_code' => 'system_entities', 'config' => json_encode(['columns' => ['code', 'name', 'table_name', 'module']]), 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'system_entities_form', 'type' => 'form', 'entity_code' => 'system_entities', 'config' => json_encode(['fields' => [
                ['name' => 'code', 'label' => 'Code (Unique)', 'type' => 'text', 'required' => true],
                ['name' => 'name', 'label' => 'Nom Affiché', 'type' => 'text', 'required' => true],
                ['name' => 'table_name', 'label' => 'Nom de la Table SQL', 'type' => 'text', 'required' => true],
                ['name' => 'module', 'label' => 'Module', 'type' => 'text', 'required' => false]
            ]]), 'created_at' => now(), 'updated_at' => now()]
        ]);

        // 3. Déclarer les Écrans pour les Champs (Grid + Form)
        DB::table('x_ui_screens')->insert([
            ['code' => 'system_fields_grid', 'type' => 'grid', 'entity_code' => 'system_fields', 'config' => json_encode(['columns' => ['entity_id', 'name', 'column_name', 'type']]), 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'system_fields_form', 'type' => 'form', 'entity_code' => 'system_fields', 'config' => json_encode(['fields' => [
                ['name' => 'entity_id', 'label' => 'ID de l\'Entité Parente', 'type' => 'text', 'required' => true],
                ['name' => 'name', 'label' => 'Nom Affiché', 'type' => 'text', 'required' => true],
                ['name' => 'column_name', 'label' => 'Nom de la Colonne SQL', 'type' => 'text', 'required' => true],
                ['name' => 'type', 'label' => 'Type SQL (string, integer, etc.)', 'type' => 'text', 'required' => true]
            ]]), 'created_at' => now(), 'updated_at' => now()]
        ]);

        // 4. Créer l'arborescence des Menus
        $parentMenuId = DB::table('x_ui_menus')->insertGetId([
            'code' => 'studio_menu', 'label' => '⚙️ Configuration ERP', 'icon' => 'cog', 'screen_id' => null, 'order' => 99, 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('x_ui_menus')->insert([
            ['code' => 'studio_entities', 'label' => 'Gestion des Entités', 'parent_id' => $parentMenuId, 'screen_id' => 'system_entities_grid', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'studio_fields', 'label' => 'Gestion des Champs', 'parent_id' => $parentMenuId, 'screen_id' => 'system_fields_grid', 'order' => 2, 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
