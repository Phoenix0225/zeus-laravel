<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Query;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Query\Condition;
use Zeus\Core\Query\EntityQuery;
use Zeus\Laravel\Query\LaravelQueryTranslator;
use Zeus\Laravel\Tests\TestCase;

class LaravelQueryTranslatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_entities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('name');
        });
    }

    public function test_it_translates_ast_to_sql_with_master_data_logic(): void
    {
        DB::table('test_entities')->insert([
            ['site_id' => 5, 'name' => 'Local'],
            ['site_id' => null, 'name' => 'Global'],
            ['site_id' => 1, 'name' => 'Autre'],
        ]);

        $entity = new EntityMetadata(
            id: 1,
            uuid: 'some-uuid',
            code: 'test_entities',
            name: 'Test Entities',
            description: null,
            module_code: 'test',
            is_active: true,
            version: 1
        );

        $query = new EntityQuery($entity);
        $query->addCondition(new Condition(field: 'site_id', operator: '=', value: 5, allowNull: true));

        $translator = new LaravelQueryTranslator();
        $builder = $translator->toBuilder($query);

        $results = $builder->get();

        $this->assertCount(2, $results);
        $names = $results->pluck('name')->toArray();
        $this->assertContains('Local', $names);
        $this->assertContains('Global', $names);
    }
}
