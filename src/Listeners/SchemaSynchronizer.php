<?php

declare(strict_types=1);

namespace Zeus\Laravel\Listeners;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Zeus\Core\Metadata\Events\FieldAddedEvent;
use Zeus\Core\Metadata\Events\FieldDeletedEvent;
use Zeus\Core\Metadata\Events\FieldUpdatedEvent;
use Zeus\Laravel\Exceptions\DataLossPreventionException;

class SchemaSynchronizer
{
    public function handleFieldAdded(FieldAddedEvent $event): void
    {
        Schema::table($event->entity->code, function (Blueprint $table) use ($event) {
            $columnName = $event->field->column_name;
            $options = $event->field->options;

            $column = match ($event->field->type) {
                \Zeus\Core\Metadata\Enums\FieldType::STRING => $table->string($columnName, $options['length'] ?? 255),
                \Zeus\Core\Metadata\Enums\FieldType::INTEGER => $table->integer($columnName),
                \Zeus\Core\Metadata\Enums\FieldType::DECIMAL => $table->decimal($columnName, $options['precision'] ?? 15, $options['scale'] ?? 4),
                \Zeus\Core\Metadata\Enums\FieldType::BOOLEAN => $table->boolean($columnName),
                \Zeus\Core\Metadata\Enums\FieldType::DATE => $table->date($columnName),
                \Zeus\Core\Metadata\Enums\FieldType::DATETIME => $table->dateTime($columnName),
                \Zeus\Core\Metadata\Enums\FieldType::TEXT => $table->text($columnName),
                \Zeus\Core\Metadata\Enums\FieldType::DICTIONARY => $table->string($columnName)->index(),
                \Zeus\Core\Metadata\Enums\FieldType::RELATION_ID => $table->unsignedBigInteger($columnName)->index(),
            };

            if (array_key_exists('default', $options)) {
                $column->default($options['default']);
            }
            // Toute colonne ajoutée à une table existante doit être nullable, 
            // SAUF si elle a une valeur par défaut stricte qui n'est pas null.
            if (!array_key_exists('default', $options) || $options['default'] === null) {
                $column->nullable();
            }
        });
    }

    public function handleFieldDeleted(FieldDeletedEvent $event): void
    {
        $hasData = DB::table($event->entity->code)
            ->whereNotNull($event->field->column_name)
            ->exists();

        if ($hasData) {
            throw new DataLossPreventionException(
                sprintf('Cannot delete column "%s" because it contains data (Data Loss Prevention).', $event->field->column_name)
            );
        }

        Schema::table($event->entity->code, function (Blueprint $table) use ($event) {
            $table->dropColumn($event->field->column_name);
        });
    }

    public function handleFieldUpdated(FieldUpdatedEvent $event): void
    {
        if ($event->originalField->column_name !== $event->newField->column_name) {
            Schema::table($event->entity->code, function (Blueprint $table) use ($event) {
                $table->renameColumn($event->originalField->column_name, $event->newField->column_name);
            });
        }
    }
}
