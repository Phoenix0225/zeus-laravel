<?php

declare(strict_types=1);

namespace Zeus\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Zeus\Laravel\Database\SchemaSynchronizer;

class SyncSchemaCommand extends Command
{
    protected $signature = 'zeus:sync-schema';

    protected $description = 'Synchronise la base de données physique avec les métadonnées No-Code';

    public function handle(SchemaSynchronizer $synchronizer): int
    {
        $this->info('⚡ Synchronisation des schémas en cours...');
        $synchronizer->syncAll();
        $this->info('✅ Base de données physique mise à jour !');

        return self::SUCCESS;
    }
}
