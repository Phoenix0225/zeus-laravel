<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Zeus\Laravel\Providers\ZeusServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            ZeusServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
