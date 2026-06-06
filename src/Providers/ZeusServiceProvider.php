<?php

declare(strict_types=1);

namespace Zeus\Laravel\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Zeus\Core\Contracts\MetadataProviderInterface;
use Zeus\Core\Contracts\StorageInterface;
use Zeus\Core\Engine\Event\EventDispatcher;
use Zeus\Core\Engine\Manager\EntityManager;
use Zeus\Core\Engine\Validation\EntityValidator;
use Zeus\Core\Kernel\ZeusKernel;
use Zeus\Core\Registry\BusinessKeyRegistry;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Registry\FieldRegistry;
use Zeus\Core\Registry\RelationRegistry;
use Zeus\Core\Metadata\Events\FieldAddedEvent;
use Zeus\Core\Metadata\Events\FieldDeletedEvent;
use Zeus\Core\Metadata\Events\FieldUpdatedEvent;
use Zeus\Laravel\Adapters\DatabaseMetadataProvider;
use Zeus\Laravel\Adapters\LaravelDatabaseStorage;
use Zeus\Laravel\Listeners\SchemaSynchronizer;

class ZeusServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/zeus.php', 'zeus'
        );

        $this->app->bind(MetadataProviderInterface::class, function ($app) {
            $databaseProvider = $app->make(DatabaseMetadataProvider::class);

            if (config('zeus.cache.enabled', false)) {
                return new \Zeus\Laravel\Adapters\CachedMetadataProvider(
                    $databaseProvider,
                    $app->make('cache.store'),
                    config('zeus.cache.prefix', 'zeus_metadata_'),
                    config('zeus.cache.ttl', 3600)
                );
            }

            return $databaseProvider;
        });
        $this->app->bind(StorageInterface::class, LaravelDatabaseStorage::class);

        $this->app->singleton(
            \Zeus\Core\Contracts\TenantContextResolverInterface::class,
            \Zeus\Laravel\Context\HttpTenantContextResolver::class
        );

        $this->app->singleton(
            \Zeus\Core\Contracts\EntityStorageInterface::class,
            \Zeus\Laravel\Storage\LaravelEntityStorage::class
        );

        $this->app->singleton(
            \Zeus\Core\Contracts\EntityQueryExecutorInterface::class,
            \Zeus\Laravel\Query\LaravelEntityQueryExecutor::class
        );

        $this->app->singleton(
            \Zeus\Core\Rules\RuleProviderInterface::class,
            \Zeus\Laravel\Rules\DatabaseRuleProvider::class
        );

        $this->app->singleton(
            \Zeus\Core\Rules\ActionResolverInterface::class,
            \Zeus\Laravel\Rules\LaravelActionResolver::class
        );

        // Bind les registres comme singletons pour qu'ils soient partagés
        $this->app->singleton(EntityRegistry::class);
        $this->app->singleton(FieldRegistry::class);
        $this->app->singleton(BusinessKeyRegistry::class);
        $this->app->singleton(RelationRegistry::class);
        $this->app->singleton(\Zeus\Core\Registry\UiRegistry::class);
        $this->app->singleton(\Zeus\Core\Security\RoleRegistry::class);        // Instanciation du Kernel avec ses 5 arguments requis
        $this->app->singleton(\Zeus\Laravel\Rules\ActionRegistry::class);
        $this->app->singleton(ZeusKernel::class, function ($app) {
            return new ZeusKernel(
                $app->make(MetadataProviderInterface::class),
                $app->make(EntityRegistry::class),
                $app->make(FieldRegistry::class),
                $app->make(BusinessKeyRegistry::class),
                $app->make(RelationRegistry::class)
            );
        });

        // Instanciation du Manager avec le EventDispatcher de Zeus Core (et non celui de Laravel)
        $this->app->singleton('zeus.manager', function ($app) {
            return new EntityManager(
                $app->make(EntityValidator::class),
                $app->make(StorageInterface::class),
                $app->make(EventDispatcher::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Zeus\Laravel\Routing\EntityRouteRegistrar::register();
        \Zeus\Laravel\Routing\UiRouteRegistrar::register();
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $roleRegistry = $this->app->make(\Zeus\Core\Security\RoleRegistry::class);
        $roleRegistry->registerRole('admin', ['*']);
        $roleRegistry->registerRole('reader', ['*.read']); // Convention pour dire "lecture sur tout"
        
        $registry = $this->app->make(\Zeus\Laravel\Rules\ActionRegistry::class);
        $registry->register(
            \App\Zeus\Actions\DummyLogAction::class, // Ou toute autre classe existante
            'Journalisation Système',
            'Ajoute une entrée dans les logs de Laravel',
            ['message' => 'string']
        );
        Event::listen(FieldAddedEvent::class, [SchemaSynchronizer::class, 'handleFieldAdded']);
        Event::listen(FieldUpdatedEvent::class, [SchemaSynchronizer::class, 'handleFieldUpdated']);
        Event::listen(FieldDeletedEvent::class, [SchemaSynchronizer::class, 'handleFieldDeleted']);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/zeus.php' => config_path('zeus.php'),
            ], 'zeus-config');

            $this->commands([
                \Zeus\Laravel\Console\ClearMetadataCacheCommand::class,
                \Zeus\Laravel\Console\Commands\InstallZeusCommand::class,
                \Zeus\Laravel\Console\Commands\SyncSchemaCommand::class,
                \Zeus\Laravel\Console\Commands\MakeActionCommand::class,
            ]);
        }

        try {
            // On vérifie que la table existe avant de forcer le Kernel à lire la base de données.
            // Cela évite de faire crasher Laravel lors des tests ou du premier "php artisan migrate".
            if (\Illuminate\Support\Facades\Schema::hasTable('x_entity')) {
                $kernel = $this->app->make(ZeusKernel::class);
                $kernel->boot();
            }
        } catch (\Exception $e) {
            // On capture silencieusement l'erreur si la connexion DB n'est pas encore configurée
        }
    }
}