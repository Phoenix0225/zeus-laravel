<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Listeners;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\Events\FieldAddedEvent;
use Zeus\Core\Metadata\Events\FieldDeletedEvent;
use Zeus\Core\Metadata\Events\FieldUpdatedEvent;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Laravel\Exceptions\DataLossPreventionException;
use Zeus\Laravel\Listeners\SchemaSynchronizer;
use Zeus\Laravel\Tests\TestCase;

class SchemaSynchronizerTest extends TestCase
{
    private SchemaSynchronizer $synchronizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->synchronizer = new SchemaSynchronizer();

        // Create the physical table for tests
        if (Schema::hasTable('test_entities')) {
            Schema::drop('test_entities');
        }

        Schema::create('test_entities', function (Blueprint $table) {
            $table->id();
        });
    }

    private function createEntity(string $code): EntityMetadata
    {
        return new EntityMetadata(
            id: 1,
            uuid: 'uuid-1',
            code: $code,
            name: ucfirst($code),
            description: null,
            module_code: 'core',
            is_active: true,
            version: 1,
        );
    }

    private function createField(string $columnName, ?\Zeus\Core\Metadata\Enums\FieldType $type = null, array $options = []): FieldMetadata
    {
        return new FieldMetadata(
            id: 1,
            uuid: 'uuid-f1',
            entity_id: 1,
            table_name: 'test_table',
            column_name: $columnName,
            type: $type ?? \Zeus\Core\Metadata\Enums\FieldType::STRING,
            label: ucfirst($columnName),
            data_type: 'string',
            length: 255,
            nullable: true,
            is_business_key: false,
            is_system: false,
            version: 1,
            options: $options,
        );
    }

    public function test_it_adds_a_new_column_to_physical_table(): void
    {
        $entity = $this->createEntity('test_entities');
        $field = $this->createField('new_col');
        $event = new FieldAddedEvent($entity, $field);

        $this->synchronizer->handleFieldAdded($event);

        $this->assertTrue(Schema::hasColumn('test_entities', 'new_col'));
    }

    public function test_it_deletes_an_empty_column_from_physical_table(): void
    {
        Schema::table('test_entities', function (Blueprint $table) {
            $table->string('empty_col')->nullable();
        });

        $entity = $this->createEntity('test_entities');
        $field = $this->createField('empty_col');
        $event = new FieldDeletedEvent($entity, $field);

        $this->synchronizer->handleFieldDeleted($event);

        $this->assertFalse(Schema::hasColumn('test_entities', 'empty_col'));
    }

    public function test_it_prevents_deletion_and_throws_exception_if_column_has_data(): void
    {
        Schema::table('test_entities', function (Blueprint $table) {
            $table->string('data_col')->nullable();
        });

        DB::table('test_entities')->insert(['data_col' => 'valeur importante']);

        $entity = $this->createEntity('test_entities');
        $field = $this->createField('data_col');
        $event = new FieldDeletedEvent($entity, $field);

        $this->expectException(DataLossPreventionException::class);

        $this->synchronizer->handleFieldDeleted($event);
    }

    public function test_it_renames_a_column_in_physical_table(): void
    {
        Schema::table('test_entities', function (Blueprint $table) {
            $table->string('old_name')->nullable();
        });

        $entity = $this->createEntity('test_entities');
        $originalField = $this->createField('old_name');
        $newField = $this->createField('new_name');
        $event = new FieldUpdatedEvent($entity, $originalField, $newField);

        $this->synchronizer->handleFieldUpdated($event);

        $this->assertFalse(Schema::hasColumn('test_entities', 'old_name'));
        $this->assertTrue(Schema::hasColumn('test_entities', 'new_name'));
    }

    public function test_it_creates_the_correct_physical_column_for_all_field_types(): void
    {
        $entity = $this->createEntity('test_entities');

        foreach (\Zeus\Core\Metadata\Enums\FieldType::cases() as $type) {
            $columnName = 'col_' . $type->value;
            $field = $this->createField($columnName, $type);
            $event = new FieldAddedEvent($entity, $field);

            $this->synchronizer->handleFieldAdded($event);

            $this->assertTrue(Schema::hasColumn('test_entities', $columnName));
        }
    }

    public function test_it_applies_field_options_like_default_value(): void
    {
        $entity = $this->createEntity('test_entities');
        
        $field = $this->createField('is_active', \Zeus\Core\Metadata\Enums\FieldType::BOOLEAN, ['default' => true]);
        $event = new FieldAddedEvent($entity, $field);

        $this->synchronizer->handleFieldAdded($event);

        DB::table('test_entities')->insert(['id' => 1]);
        $row = DB::table('test_entities')->first();

        // Check that default value was correctly applied to the inserted row.
        $this->assertEquals(1, $row->is_active);
    }
}
