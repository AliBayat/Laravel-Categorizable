<?php

declare(strict_types=1);

/**
 * Laravel Categorizable Package by Ali Bayat.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Kalnoy\Nestedset\NestedSet;

class CreateCategoriesTables extends Migration
{
    public function up()
    {
        Schema::create(config('laravel-categorizable.table_names.main_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->string('type')->default('default');
            NestedSet::columns($table);
            $table->timestamps();
        });

        Schema::create(config('laravel-categorizable.table_names.morph_table'), function (Blueprint $table) {
            $table->integer('category_id');
            $table->morphs('model');
        });


        
    }

    public function down()
    {
        Schema::dropIfExists(config('laravel-categorizable.table_names.main_table'));
        Schema::dropIfExists(config('laravel-categorizable.table_names.morph_table'));
    }
}
