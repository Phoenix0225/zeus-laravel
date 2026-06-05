<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Console;

use Illuminate\Support\Facades\Schema;
use Zeus\Laravel\Tests\TestCase;

class InstallZeusCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations();
    }

    public function test_install_command_runs_migrations_and_outputs_success(): void
    {
        $this->artisan('zeus:install')
             ->expectsConfirmation('Voulez-vous exécuter les migrations système de Zeus ?', 'yes')
             ->expectsOutput('✅ Installation terminée avec succès !')
             ->assertSuccessful();

        $this->assertTrue(Schema::hasTable('zeus_tenants'));
        $this->assertTrue(Schema::hasTable('zeus_tenant_user'));

        $this->assertDatabaseHas('users', ['email' => 'admin@zeus.local']);
        $this->assertDatabaseHas('zeus_tenants', ['code' => 'HQ']);
        $this->assertDatabaseHas('zeus_tenant_user', ['role' => 'admin']);
    }
}
