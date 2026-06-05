<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Http;

use Zeus\Core\Registry\UiRegistry;
use Zeus\Core\UI\MenuNode;
use Zeus\Core\UI\ScreenMetadata;
use Zeus\Laravel\Tests\TestCase;

class UiApiTest extends TestCase
{
    public function test_it_exposes_ui_configuration_and_screens(): void
    {
        /** @var UiRegistry $registry */
        $registry = $this->app->make(UiRegistry::class);

        $registry->registerMenu(new MenuNode(id: 'home', label: 'Accueil', icon: 'home', screenId: 'home_dashboard'));
        $registry->registerScreen(new ScreenMetadata(id: 'home_dashboard', type: 'dashboard'));

        $response = $this->getJson('/api/ui/config');
        $response->assertStatus(200);
        $response->assertJsonPath('menus.0.id', 'home');

        $screenResponse = $this->getJson('/api/ui/screens/home_dashboard');
        $screenResponse->assertStatus(200);
        $screenResponse->assertJsonPath('type', 'dashboard');
    }
}
