<?php

declare(strict_types=1);

namespace Zeus\Laravel\Adapters;

use Illuminate\Contracts\Cache\Repository;
use Zeus\Core\Contracts\MetadataProviderInterface;

class CachedMetadataProvider implements MetadataProviderInterface
{
    public function __construct(
        private readonly MetadataProviderInterface $innerProvider,
        private readonly Repository $cache,
        private readonly string $prefix = 'zeus_metadata_',
        private readonly int $ttl = 3600
    ) {
    }

    public function getEntities(): iterable
    {
        return $this->cache->remember($this->prefix . 'entities', $this->ttl, function () {
            return iterator_to_array($this->innerProvider->getEntities());
        });
    }

    public function getFields(): iterable
    {
        return $this->cache->remember($this->prefix . 'fields', $this->ttl, function () {
            return iterator_to_array($this->innerProvider->getFields());
        });
    }

    public function getBusinessKeys(): iterable
    {
        return $this->cache->remember($this->prefix . 'business_keys', $this->ttl, function () {
            return iterator_to_array($this->innerProvider->getBusinessKeys());
        });
    }

    public function getRelations(): iterable
    {
        return $this->cache->remember($this->prefix . 'relations', $this->ttl, function () {
            return iterator_to_array($this->innerProvider->getRelations());
        });
    }
}
