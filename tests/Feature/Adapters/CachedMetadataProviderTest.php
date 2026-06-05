<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Adapters;

use Zeus\Laravel\Tests\TestCase;
use Zeus\Core\Contracts\MetadataProviderInterface;
use Zeus\Laravel\Adapters\CachedMetadataProvider;

class CachedMetadataProviderTest extends TestCase
{
    public function test_it_caches_metadata_and_avoids_hitting_inner_provider(): void
    {
        $innerProvider = $this->createMock(MetadataProviderInterface::class);

        // On s'attend à ce que l'inner provider soit appelé exactement une fois
        $innerProvider->expects($this->once())
            ->method('getEntities')
            ->willReturnCallback(function () {
                yield from [];
            });

        // Utilisation du store array pour isoler les tests
        $cache = $this->app->make('cache')->store('array');

        $provider = new CachedMetadataProvider(
            $innerProvider,
            $cache,
            'test_zeus_',
            60
        );

        // Premier appel : le mock sera sollicité
        $result1 = $provider->getEntities();

        // Deuxième appel : le mock NE DOIT PAS être appelé, la donnée vient du cache
        $result2 = $provider->getEntities();

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertEmpty($result1);
        $this->assertEmpty($result2);
    }
}
