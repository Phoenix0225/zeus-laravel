<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Security;

use Illuminate\Support\Facades\DB;
use Zeus\Core\Contracts\TenantContextResolverInterface;
use Zeus\Laravel\Tests\TestCase;

class RoleResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loadLaravelMigrations();
        $this->artisan('zeus:install')->expectsConfirmation('Voulez-vous exécuter les migrations système de Zeus ?', 'yes');
    }

    public function test_resolver_injects_permissions_based_on_database_role(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);

        DB::table('zeus_tenants')->insert([
            'id' => 5,
            'code' => 'MTL-01',
            'name' => 'Usine MTL',
            'is_active' => true
        ]);

        DB::table('zeus_tenant_user')->insert([
            'user_id' => $userId,
            'tenant_id' => 5,
            'role' => 'admin'
        ]);

        $this->actingAs(\Illuminate\Foundation\Auth\User::find($userId));

        $this->app['request']->headers->set('X-Site-Id', '5');

        $resolver = $this->app->make(TenantContextResolverInterface::class);
        $context = $resolver->resolve();

        $this->assertTrue($context->hasPermission('any_entity.create'));
    }
}
