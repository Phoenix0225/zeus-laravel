<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Rules;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Zeus\Core\EntityManager;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Query\EntityRecord;
use Zeus\Core\Rules\ActionInterface;
use Zeus\Laravel\Tests\TestCase;

class TestFlagAction implements ActionInterface
{
    public static bool $executed = false;
    public static array $receivedParams = [];

    public function execute(EntityRecord $record, array $params): void
    {
        self::$executed = true;
        self::$receivedParams = $params;
    }
}

class BusinessRuleIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestFlagAction::$executed = false;
        TestFlagAction::$receivedParams = [];
    }

    public function test_it_loads_rules_from_database_and_executes_action_on_entity_creation(): void
    {
        Schema::create('test_invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            $table->timestamps();
        });

        DB::table('zeus_business_rules')->insert([
            'entity_code' => 'test_invoices',
            'trigger_event' => 'after_create',
            'conditions' => json_encode([]),
            'actions' => json_encode([
                ['class' => TestFlagAction::class, 'params' => ['alert' => 'yes']]
            ]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $entityManager = $this->app->make(EntityManager::class);
        $entity = new EntityMetadata('test_invoices', 'test_invoices');

        $entityManager->create($entity, ['amount' => 100]);

        $this->assertTrue(TestFlagAction::$executed, "L'action n'a pas été déclenchée !");
        $this->assertEquals('yes', TestFlagAction::$receivedParams['alert']);
    }
}
