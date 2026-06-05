<?php

declare(strict_types=1);

namespace Zeus\Laravel\Adapters;

use Illuminate\Support\Facades\DB;
use Zeus\Core\Contracts\MetadataProviderInterface;
use Zeus\Core\DTO\EntityMetadata;
use Zeus\Core\DTO\FieldMetadata;
use Zeus\Core\DTO\BusinessKeyMetadata;
use Zeus\Core\DTO\RelationMetadata;

class DatabaseMetadataProvider implements MetadataProviderInterface
{
    private ?string $connection;

    public function __construct()
    {
        $this->connection = config('zeus.connection');
    }

    /**
     * @return iterable<EntityMetadata>
     */
    public function getEntities(): iterable
    {
        $records = DB::connection($this->connection)->table('x_entity')->get();

        foreach ($records as $record) {
            yield new EntityMetadata(
                id: (int) $record->id,
                uuid: (string) $record->uuid,
                code: (string) $record->code,
                name: (string) $record->name,
                description: $record->description !== null ? (string) $record->description : null,
                module_code: (string) $record->module_code,
                is_active: (bool) $record->is_active,
                version: (int) $record->version,
            );
        }
    }

    /**
     * @return iterable<FieldMetadata>
     */
    public function getFields(): iterable
    {
        $records = DB::connection($this->connection)->table('x_field')->get();

        foreach ($records as $record) {
            yield new FieldMetadata(
                id: (int) $record->id,
                entityId: (int) $record->entity_id,
                name: (string) $record->name,
                type: (string) $record->type,
            );
        }
    }

    /**
     * @return iterable<BusinessKeyMetadata>
     */
    public function getBusinessKeys(): iterable
    {
        $records = DB::connection($this->connection)->table('x_business_key')->get();

        foreach ($records as $record) {
            yield new BusinessKeyMetadata(
                id: (int) $record->id,
                entityId: (int) $record->entity_id,
                name: (string) $record->name,
                fields: is_string($record->fields) ? json_decode($record->fields, true) : (array) $record->fields,
            );
        }
    }

    /**
     * @return iterable<RelationMetadata>
     */
    public function getRelations(): iterable
    {
        $records = DB::connection($this->connection)->table('x_relation')->get();

        foreach ($records as $record) {
            yield new RelationMetadata(
                id: (int) $record->id,
                sourceEntityId: (int) $record->source_entity_id,
                targetEntityId: (int) $record->target_entity_id,
                type: (string) $record->type,
                foreignKey: (string) $record->foreign_key,
            );
        }
    }
}
