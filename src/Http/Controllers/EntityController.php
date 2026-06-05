<?php

declare(strict_types=1);

namespace Zeus\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Zeus\Core\EntityManager;
use Zeus\Core\EntityReader;
use Zeus\Core\Query\EntityQueryBuilder;
use Zeus\Core\Query\EntityRecord;
use Zeus\Core\Registry\EntityRegistry;

class EntityController extends Controller
{
    public function __construct(
        private readonly EntityRegistry $entityRegistry,
        private readonly EntityManager $entityManager,
        private readonly EntityQueryBuilder $queryBuilder,
        private readonly EntityReader $reader
    ) {}

    public function index(Request $request, string $entityCode): JsonResponse
    {
        $entity = $this->entityRegistry->get($entityCode);
        
        if (!$entity) {
            return response()->json(['error' => 'Entity not found'], 404);
        }
        
        $query = $this->queryBuilder->forEntity($entity)->getQuery();
        $records = $this->reader->fetch($query);

        return response()->json(array_map(fn(EntityRecord $r) => $r->data, $records));
    }

    public function store(Request $request, string $entityCode): JsonResponse
    {
        $entity = $this->entityRegistry->get($entityCode);
        if (!$entity) {
            return response()->json(['error' => 'Entity not found'], 404);
        }
        
        $id = $this->entityManager->create($entity, $request->all());

        return response()->json(['id' => $id], 201);
    }

    public function update(Request $request, string $entityCode, string $id): JsonResponse
    {
        $entity = $this->entityRegistry->get($entityCode);
        if (!$entity) {
            return response()->json(['error' => 'Entity not found'], 404);
        }
        
        $success = $this->entityManager->update($entity, $id, $request->all());

        return response()->json(['success' => $success]);
    }

    public function destroy(Request $request, string $entityCode, string $id): JsonResponse
    {
        $entity = $this->entityRegistry->get($entityCode);
        if (!$entity) {
            return response()->json(['error' => 'Entity not found'], 404);
        }
        
        $this->entityManager->delete($entity, $id);

        return response()->json(null, 204);
    }
}
