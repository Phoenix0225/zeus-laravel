<?php

declare(strict_types=1);

namespace Zeus\Laravel\Adapters;

use Illuminate\Support\Facades\DB;
use Zeus\Core\Contracts\StorageInterface;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Engine\Query\EntityQuery;

class LaravelDatabaseStorage implements StorageInterface
{
    private EntityRegistry $entityRegistry;

    public function __construct(EntityRegistry $entityRegistry)
    {
        $this->entityRegistry = $entityRegistry;
    }

    public function insert(string $entityCode, array $data): int|string
    {
        $entity = $this->entityRegistry->get($entityCode);
        
        return DB::table($entity->code)->insertGetId($data);
    }

    public function update(string $entityCode, int|string $id, array $data): bool
    {
        $entity = $this->entityRegistry->get($entityCode);
        
        return (bool) DB::table($entity->code)->where('id', $id)->update($data);
    }

    public function delete(string $entityCode, int|string $id): bool
    {
        $entity = $this->entityRegistry->get($entityCode);
        
        return (bool) DB::table($entity->code)->where('id', $id)->delete();
    }

    public function query(EntityQuery $query): array
    {
        $entity = $query->entity;
        $dbQuery = DB::table($entity->code);

        $selectedFields = $query->selectedFields;
        if (!empty($selectedFields)) {
            $dbQuery->select($selectedFields);
        }

        foreach ($query->criteria as $criteria) {
            $dbQuery->where($criteria->field, $criteria->operator, $criteria->value);
        }

        return $dbQuery->get()->map(fn($row) => (array) $row)->toArray();
    }
}
