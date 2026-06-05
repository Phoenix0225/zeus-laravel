<?php

declare(strict_types=1);

namespace Zeus\Laravel\Storage;

use Illuminate\Support\Facades\DB;
use Zeus\Core\Contracts\EntityStorageInterface;
use Zeus\Core\Metadata\EntityMetadata;

class LaravelEntityStorage implements EntityStorageInterface
{
    public function insert(EntityMetadata $entity, array $payload): string|int
    {
        return DB::table($entity->code)->insertGetId($payload);
    }

    public function update(EntityMetadata $entity, string|int $id, array $payload, array $tenantCriteria): bool
    {
        $query = DB::table($entity->code);

        foreach ($tenantCriteria as $column => $value) {
            $query->where($column, $value);
        }

        $updatedRows = $query->where('id', $id)->update($payload);

        return $updatedRows > 0;
    }

    public function delete(EntityMetadata $entity, string|int $id, array $tenantCriteria): bool
    {
        $query = DB::table($entity->code);

        foreach ($tenantCriteria as $column => $value) {
            $query->where($column, $value);
        }

        $deletedRows = $query->where('id', $id)->delete();

        return $deletedRows > 0;
    }
}
