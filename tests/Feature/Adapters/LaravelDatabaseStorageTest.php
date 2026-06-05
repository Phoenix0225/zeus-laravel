<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Adapters;

use Illuminate\Support\Facades\DB;
use Zeus\Core\Contracts\StorageInterface;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Engine\Query\EntityQuery;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Laravel\Tests\TestCase;

class LaravelDatabaseStorageTest extends TestCase
{
    private StorageInterface $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $entityMetadata = new EntityMetadata(
            id: 1,
            uuid: '123e4567-e89b-12d3-a456-426614174000',
            code: 'test_customers',
            name: 'Customer',
            description: 'Test entity',
            module_code: 'TEST',
            is_active: true,
            version: 1,
        );

        $entityRegistry = $this->app->make(EntityRegistry::class);
        $entityRegistry->register($entityMetadata);

        $this->storage = $this->app->make(StorageInterface::class);
    }

    public function test_it_can_insert_and_retrieve_data(): void
    {
        // Insert data via Storage Adapter
        $id = $this->storage->insert('test_customers', [
            'code' => 'CUST-001',
            'name' => 'John Doe'
        ]);

        $this->assertNotEmpty($id);

        // Verify data via Laravel DB Facade
        $record = DB::table('test_customers')->first();
        
        $this->assertNotNull($record);
        $this->assertEquals('CUST-001', $record->code);
        $this->assertEquals('John Doe', $record->name);
    }

    public function test_it_translates_entity_query_to_sql(): void
    {
        // Setup initial data
        DB::table('test_customers')->insert([
            ['code' => 'CUST-001', 'name' => 'John Doe'],
            ['code' => 'CUST-002', 'name' => 'Jane Smith'],
            ['code' => 'CUST-003', 'name' => 'Bob Builder'],
        ]);

        $entityRegistry = $this->app->make(EntityRegistry::class);
        $entity = $entityRegistry->get('test_customers');

        // Create the AST Query
        $query = new EntityQuery($entity);
        $query->addCriteria(new \Zeus\Core\Engine\Query\QueryCriteria('code', '=', 'CUST-002'));

        // Execute via Storage Adapter
        $results = $this->storage->query($query);

        // Assertions
        $this->assertCount(1, $results);
        $this->assertEquals('CUST-002', $results[0]['code']);
        $this->assertEquals('Jane Smith', $results[0]['name']);
    }
}
