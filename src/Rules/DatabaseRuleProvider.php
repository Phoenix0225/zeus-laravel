<?php

declare(strict_types=1);

namespace Zeus\Laravel\Rules;

use Illuminate\Support\Facades\DB;
use Zeus\Core\Rules\RuleMetadata;
use Zeus\Core\Rules\RuleProviderInterface;

class DatabaseRuleProvider implements RuleProviderInterface
{
    public function getRulesFor(string $entityCode, string $trigger): array
    {
        $rows = DB::table('zeus_business_rules')
            ->where('entity_code', $entityCode)
            ->where('trigger_event', $trigger)
            ->where('is_active', true)
            ->get();

        $rules = [];

        foreach ($rows as $row) {
            $rules[] = new RuleMetadata(
                trigger: $row->trigger_event,
                entityCode: $row->entity_code,
                conditions: json_decode($row->conditions ?? '[]', true),
                actions: json_decode($row->actions, true)
            );
        }

        return $rules;
    }
}
