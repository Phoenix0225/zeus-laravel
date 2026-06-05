<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Storage;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Laravel\Storage\LaravelEntityStorage;
use Zeus\Laravel\Providers\ZeusServiceProvider;

class LaravelEntityStorageTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ZeusServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('site_id')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_entities');

        parent::tearDown();
    }

    public function test_it_inserts_data_into_physical_db(): void
    {
        $storage = new LaravelEntityStorage();
        $entity = new EntityMetadata(
            id: 1,
            uuid: 'test-uuid-1',
            code: 'test_entities',
            name: 'Test Entities',
            description: null,
            module_code: 'test_module',
            is_active: true,
            version: 1
        );

        $payload = [
            'name' => 'test insert',
            'site_id' => 1,
        ];

        $id = $storage->insert($entity, $payload);

        $this->assertIsInt($id);
        $this->assertTrue(DB::table('test_entities')->where('id', $id)->exists());
    }

    public function test_it_updates_only_data_matching_tenant_criteria(): void
    {
        DB::table('test_entities')->insert([
            'id' => 1,
            'name' => 'original',
            'site_id' => 5,
        ]);

        $storage = new LaravelEntityStorage();
        $entity = new EntityMetadata(
            id: 1,
            uuid: 'test-uuid-1',
            code: 'test_entities',
            name: 'Test Entities',
            description: null,
            module_code: 'test_module',
            is_active: true,
            version: 1
        );

        // Tentative de mise à jour avec les bons critères
        $result = $storage->update($entity, 1, ['name' => 'updated'], ['site_id' => 5]);

        $this->assertTrue($result);
        $this->assertEquals('updated', DB::table('test_entities')->where('id', 1)->value('name'));

        // Tentative de mise à jour avec des critères restreignant au site_id = 999 (inexistant)
        $resultFail = $storage->update($entity, 1, ['name' => 'hacked'], ['site_id' => 999]);

        $this->assertFalse($resultFail);
        $this->assertEquals('updated', DB::table('test_entities')->where('id', 1)->value('name'));
    }
}
