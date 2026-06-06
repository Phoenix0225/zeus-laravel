<?php

declare(strict_types=1);

namespace Zeus\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeActionCommand extends Command
{
    protected $signature = 'zeus:make-action {name}';

    protected $description = 'Génère une nouvelle classe Action pour Zeus ERP';

    public function handle(): int
    {
        $name = $this->argument('name');
        $directory = app_path('Zeus/Actions');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filePath = $directory . '/' . $name . '.php';

        if (File::exists($filePath)) {
            $this->error("L'action {$name} existe déjà !");
            return self::FAILURE;
        }

        $stub = <<<PHP
<?php

declare(strict_types=1);

namespace App\Zeus\Actions;

use Zeus\Core\Rules\Contracts\ActionInterface;
use Zeus\Core\Query\EntityRecord;

class {$name} implements ActionInterface 
{
    public function execute(EntityRecord \$record, array \$params): void 
    {
        // Ta logique d'affaires complexe en pur PHP ici !
    }
}
PHP;

        File::put($filePath, $stub);

        $this->info("✅ Action {$name} générée avec succès : {$filePath}");

        return self::SUCCESS;
    }
}
