<?php

declare(strict_types=1);

namespace Zeus\Laravel\Query;

use Zeus\Core\Query\EntityQuery;
use Zeus\Core\Query\Condition;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class LaravelQueryTranslator
{
    public function toBuilder(EntityQuery $query): Builder
    {
        $builder = DB::table($query->getEntity()->code);

        foreach ($query->getConditions() as $c) {
            if ($c->operator === 'IN') {
                $builder->whereIn($c->field, $c->value);
                continue;
            }

            if ($c->allowNull) {
                $builder->where(function ($q) use ($c) {
                    $q->where($c->field, $c->operator, $c->value)
                      ->orWhereNull($c->field);
                });
            } else {
                $builder->where($c->field, $c->operator, $c->value);
            }
        }

        return $builder;
    }
}
