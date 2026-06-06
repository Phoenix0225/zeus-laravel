<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Database;

use Illuminate\Support\Facades\Schema;
use Zeus\Laravel\Tests\TestCase;

class MetadataDictionaryTest extends TestCase
{
    public function test_metadata_tables_are_created_successfully(): void
    {
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable('x_entities'));
        $this->assertTrue(Schema::hasTable('x_fields'));
        $this->assertTrue(Schema::hasTable('x_ui_screens'));
        $this->assertTrue(Schema::hasTable('x_ui_menus'));
    }

    public function test_seeder_populates_default_metadata_correctly(): void
    {
        $this->artisan('migrate');
        $this->artisan('db:seed', ['--class' => '\\Zeus\\Laravel\\Database\\Seeders\\ZeusDatabaseSeeder']);

        $this->assertDatabaseHas('x_entities', ['code' => 'users']);
        $this->assertDatabaseHas('x_fields', ['column_name' => 'email']);
        $this->assertDatabaseHas('x_ui_screens', ['code' => 'users_grid']);
        $this->assertDatabaseHas('x_ui_menus', ['code' => 'users_menu']);
    }
}
