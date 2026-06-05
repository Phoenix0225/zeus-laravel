<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Console;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Zeus\Laravel\Tests\TestCase;

class ClearMetadataCacheCommandTest extends TestCase
{
    public function test_it_clears_the_metadata_cache_keys(): void
    {
        $prefix = 'test_zeus_';
        Config::set('zeus.cache.prefix', $prefix);

        // On injecte manuellement des données dans le cache pour les 4 clés
        Cache::put($prefix . 'entities', ['dummy_entities'], 60);
        Cache::put($prefix . 'fields', ['dummy_fields'], 60);
        Cache::put($prefix . 'business_keys', ['dummy_bkeys'], 60);
        Cache::put($prefix . 'relations', ['dummy_relations'], 60);

        // On vérifie que les données sont bien dans le cache
        $this->assertTrue(Cache::has($prefix . 'entities'));
        $this->assertTrue(Cache::has($prefix . 'fields'));
        $this->assertTrue(Cache::has($prefix . 'business_keys'));
        $this->assertTrue(Cache::has($prefix . 'relations'));

        // On exécute la commande et on vérifie son succès ainsi que sa sortie
        $this->artisan('zeus:clear-cache')
            ->expectsOutput('Le cache des métadonnées Zeus a été vidé avec succès.')
            ->assertSuccessful();

        // On vérifie que les clés ont bien été supprimées du cache
        $this->assertFalse(Cache::has($prefix . 'entities'));
        $this->assertFalse(Cache::has($prefix . 'fields'));
        $this->assertFalse(Cache::has($prefix . 'business_keys'));
        $this->assertFalse(Cache::has($prefix . 'relations'));
    }
}
