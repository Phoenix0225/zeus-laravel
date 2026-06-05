<?php

declare(strict_types=1);

namespace Zeus\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearMetadataCacheCommand extends Command
{
    protected $signature = 'zeus:clear-cache';

    protected $description = 'Vide le cache des métadonnées du moteur Zeus Core.';

    public function handle(): int
    {
        $prefix = config('zeus.cache.prefix', 'zeus_metadata_');

        Cache::forget($prefix . 'entities');
        Cache::forget($prefix . 'fields');
        Cache::forget($prefix . 'business_keys');
        Cache::forget($prefix . 'relations');

        $this->info('Le cache des métadonnées Zeus a été vidé avec succès.');

        return Command::SUCCESS;
    }
}
