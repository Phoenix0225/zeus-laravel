<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Http;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Zeus\Core\Contracts\MetadataProviderInterface;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Laravel\Tests\TestCase;

class EntityApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_api_entities', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('site_id');
            $table->string('name');
        });

        $entityRegistry = $this->createMock(EntityRegistry::class);
        $entity = new EntityMetadata(
            id: 1,
            uuid: 'uuid-123',
            code: 'test_api_entities',
            name: 'API Entity',
            description: null,
            module_code: 'api',
            is_active: true,
            version: 1
        );
        $entityRegistry->method('get')->with('test_api_entities')->willReturn($entity);
        $entityRegistry->method('all')->willReturn(['test_api_entities' => $entity]);

        $this->app->instance(EntityRegistry::class, $entityRegistry);

        $metadataProvider = $this->createMock(MetadataProviderInterface::class);
        $fieldMock = new \stdClass();
        $fieldMock->column_name = 'name';
        $fieldMock2 = new \stdClass();
        $fieldMock2->column_name = 'site_id';
        
        $metadataProvider->method('getFields')->willReturn([$fieldMock, $fieldMock2]);
        $this->app->instance(MetadataProviderInterface::class, $metadataProvider);
    }

    public function test_dynamic_api_creates_and_fetches_records_with_tenant_scoping(): void
    {
        // L'Écriture
        $response = $this->postJson('/api/dynamic/test_api_entities', ['name' => 'Produit A'], [
            'X-Company-Id' => 'C-1',
            'X-Site-Id' => 'MTL-01'
        ]);
        
        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertNotNull($id);

        $record = DB::table('test_api_entities')->where('id', $id)->first();
        $this->assertEquals('Produit A', $record->name);
        $this->assertEquals('MTL-01', $record->site_id);

        // La Lecture
        $response = $this->getJson('/api/dynamic/test_api_entities', [
            'X-Company-Id' => 'C-1',
            'X-Site-Id' => 'MTL-01'
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Produit A']);

        // L'Isolation
        $response = $this->getJson('/api/dynamic/test_api_entities', [
            'X-Company-Id' => 'C-1',
            'X-Site-Id' => 'QC-02'
        ]);
        
        $response->assertStatus(200);
        $this->assertEmpty($response->json());
    }
}
