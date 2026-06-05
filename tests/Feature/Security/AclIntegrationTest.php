<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Security;

use Zeus\Core\Context\TenantContext;
use Zeus\Core\Contracts\TenantContextResolverInterface;
use Zeus\Core\Exceptions\UnauthorizedActionException;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\EntityReader;
use Zeus\Core\Query\EntityQuery;
use Zeus\Laravel\Tests\TestCase;

class AclIntegrationTest extends TestCase
{
    public function test_api_blocks_unauthorized_actions_and_allows_authorized_ones(): void
    {
        $this->withoutExceptionHandling();

        $entity = new EntityMetadata(1, 'uuid', 'test_acl_entities', 'Test', 'Desc', 'core', true, 1);

        // Mock du registre d'entités pour que l'API trouve l'entité
        $entityRegistryMock = $this->mock(EntityRegistry::class);
        $entityRegistryMock->shouldReceive('get')->with('test_acl_entities')->andReturn($entity);

        // Mock du EntityReader pour le GET index (qui return un tableau vide)
        $readerMock = $this->mock(EntityReader::class);
        $readerMock->shouldReceive('fetch')->andReturn([]);

        // Mock du TenantContextResolver
        $this->mock(TenantContextResolverInterface::class, function ($mock) {
            $context = new TenantContext(permissions: ['test_acl_entities.read']);
            $mock->shouldReceive('resolve')->andReturn($context);
        });

        // Test GET (Read) - Doit être autorisé
        $response = $this->getJson('/api/dynamic/test_acl_entities');
        $response->assertStatus(200);

        // Test POST (Create) - Doit être bloqué
        $this->expectException(UnauthorizedActionException::class);
        $this->postJson('/api/dynamic/test_acl_entities', ['name' => 'Hack']);
    }
}
