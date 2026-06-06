<?php

declare(strict_types=1);

namespace Zeus\Laravel\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
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
    protected $description = 'Installe l\'Olympe (Zeus ERP) avec migrations, seeders et frontend';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('⚡ Installation de l\'Olympe (Zeus ERP) en cours...');

        // 1. Publication des assets frontend
        $this->comment('Publication de l\'interface Vue 3...');
        $this->callSilent('vendor:publish', ['--tag' => 'zeus-frontend', '--force' => true]);

        // 2. Exécution des migrations système
        $this->comment('Forge des tables système en cours...');
        $this->call('migrate', ['--force' => true]);

        // 3. Injection du dictionnaire (Seeders)
        $this->comment('Injection du dictionnaire No-Code...');
        $this->call('db:seed', ['--class' => '\\Zeus\\Laravel\\Database\\Seeders\\ZeusDatabaseSeeder', '--force' => true]);

        $this->info('✅ L\'installation est terminée avec succès. Bienvenue dans Zeus !');
        $this->line('N\'oubliez pas de lancer "npm install && npm run dev".');

        return self::SUCCESS;
    }
}
