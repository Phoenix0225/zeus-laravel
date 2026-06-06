<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Zeus\Laravel\Tests\TestCase;

class SchemaSynchronizerTest extends TestCase
{
    public function test_it_creates_physical_tables_and_columns_from_metadata(): void
    {
        $this->artisan('migrate');

        // Ajout d'un tenant factice
        $tenantId = DB::table('zeus_tenants')->insertGetId([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Ajout de l'entité
        $entityId = DB::table('x_entities')->insertGetId([
            'code' => 'products',
            'name' => 'Produits',
            'table_name' => 'test_products',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Ajout du champ
        DB::table('x_fields')->insert([
            'entity_id' => $entityId,
            'name' => 'Prix',
            'column_name' => 'price',
            'type' => 'integer',
            'is_required' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Exécution de la commande
        $this->artisan('zeus:sync-schema')->assertSuccessful();

        // Assertions
        $this->assertTrue(Schema::hasTable('test_products'));
        $this->assertTrue(Schema::hasColumn('test_products', 'tenant_id'));
        $this->assertTrue(Schema::hasColumn('test_products', 'price'));
    }
}
