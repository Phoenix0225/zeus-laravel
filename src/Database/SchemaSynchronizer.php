<?php

declare(strict_types=1);

namespace Zeus\Laravel\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaSynchronizer
{
    public function syncAll(): void
    {
        $entities = DB::table('x_entities')->where('is_active', true)->get();

        foreach ($entities as $entity) {
            if (!Schema::hasTable($entity->table_name)) {
                Schema::create($entity->table_name, function (Blueprint $table) {
                    $table->id();
                    // Injection automatique de la clé multi-tenant
                    $table->foreignId('tenant_id')->constrained('zeus_tenants')->cascadeOnDelete();
                    $table->timestamps();
                });
            }

            $fields = DB::table('x_fields')->where('entity_id', $entity->id)->get();

            Schema::table($entity->table_name, function (Blueprint $table) use ($fields, $entity) {
                foreach ($fields as $field) {
                    if (!Schema::hasColumn($entity->table_name, $field->column_name)) {
                        $column = $table->{$field->type}($field->column_name);
                        if (!$field->is_required) {
                            $column->nullable();
                        }
                    }
                }
            });
        }
    }
}
