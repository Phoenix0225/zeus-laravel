<?php

declare(strict_types=1);

namespace Zeus\Laravel\Console\Commands;

use Illuminate\Console\Command;

class InstallZeusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zeus:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installe les fondations du framework Zeus';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('⚡ Installation de Zeus Framework...');

        if (! $this->confirm('Voulez-vous exécuter les migrations système de Zeus ?')) {
            return self::SUCCESS;
        }

        $this->call('migrate');

        $this->info('🌱 Injection des données de base (Seeding)...');
        $this->call('db:seed', [
            '--class' => '\\Zeus\\Laravel\\Database\\Seeders\\ZeusDatabaseSeeder'
        ]);

        $this->info('✅ Installation terminée avec succès !');

        return self::SUCCESS;
    }
}
