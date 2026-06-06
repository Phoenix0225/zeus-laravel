<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('x_entities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('table_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('x_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('x_entities')->cascadeOnDelete();
            $table->string('name');
            $table->string('column_name');
            $table->string('type');
            $table->boolean('is_required');
            $table->timestamps();
        });

        Schema::create('x_ui_screens', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type');
            $table->string('entity_code')->nullable();
            $table->json('config');
            $table->timestamps();
        });

        Schema::create('x_ui_menus', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('x_ui_menus')->cascadeOnDelete();
            $table->string('screen_id')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('x_ui_menus');
        Schema::dropIfExists('x_ui_screens');
        Schema::dropIfExists('x_fields');
        Schema::dropIfExists('x_entities');
    }
};
