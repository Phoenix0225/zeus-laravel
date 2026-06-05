<?php

declare(strict_types=1);

namespace Zeus\Laravel\Query;

use Zeus\Core\Contracts\EntityQueryExecutorInterface;
use Zeus\Core\Query\EntityQuery;

class LaravelEntityQueryExecutor implements EntityQueryExecutorInterface
{
    public function __construct(
        private readonly LaravelQueryTranslator $translator
    ) {}

    public function execute(EntityQuery $query): array
    {
        $builder = $this->translator->toBuilder($query);
        
        return $builder->get()->map(fn(object $item) => (array) $item)->toArray();
    }
}
