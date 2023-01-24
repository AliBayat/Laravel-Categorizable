<?php

declare(strict_types=1);

/**
 * Laravel Categorizable Package by Ali Bayat.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('laravel-categorizable.table_names.main_table'), static function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->string('type')->default('default');
            NestedSet::columns($table);
            $table->timestamps();
        });

        Schema::create(config('laravel-categorizable.table_names.morph_table'), static function (Blueprint $table) {
            $table->integer('category_id');
            $table->morphs('model');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('laravel-categorizable.table_names.main_table'));
        Schema::dropIfExists(config('laravel-categorizable.table_names.morph_table'));
    }
};
