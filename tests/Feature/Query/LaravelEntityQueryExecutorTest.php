<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Query;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Query\EntityQuery;
use Zeus\Laravel\Query\LaravelEntityQueryExecutor;
use Zeus\Laravel\Query\LaravelQueryTranslator;
use Zeus\Laravel\Tests\TestCase;

class LaravelEntityQueryExecutorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_executor_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
    }

    public function test_it_executes_query_and_returns_array_of_arrays(): void
    {
        DB::table('test_executor_entities')->insert([
            ['name' => 'First'],
            ['name' => 'Second'],
        ]);

        $entity = new EntityMetadata(
            id: 1,
            uuid: 'uuid',
            code: 'test_executor_entities',
            name: 'Test',
            description: null,
            module_code: 'test',
            is_active: true,
            version: 1
        );

        $query = new EntityQuery($entity);
        $translator = new LaravelQueryTranslator();
        $executor = new LaravelEntityQueryExecutor($translator);

        $results = $executor->execute($query);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertIsArray($results[0]);
        $this->assertIsArray($results[1]);
        $this->assertEquals('First', $results[0]['name']);
        $this->assertEquals('Second', $results[1]['name']);
    }
}
