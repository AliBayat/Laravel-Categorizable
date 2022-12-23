<?php

namespace AliBayat\LaravelCategorizable\Tests;

use AliBayat\LaravelCategorizable\CategorizableServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\{Application, Testing\DatabaseMigrations};
use Kalnoy\Nestedset\NestedSet;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	use DatabaseMigrations;
	
	/**
	 * @var string|ApplicationContract|Repository
	 */
	protected string|ApplicationContract|Repository $mainTable;
	
	/**
	 * @var string|ApplicationContract|Repository
	 */
	protected string|ApplicationContract|Repository $morphTable;
	
	/**
	 * @return void
	 */
    public function setUp(): void
    {
        parent::setUp();
		$this->mainTable = config('laravel-categorizable.table_names.main_table');
		$this->morphTable = config('laravel-categorizable.table_names.morph_table');
        $this->setUpDatabase();
    }
	
	/**
	 * @param Application $app
	 * @return array
	 */
    protected function getPackageProviders($app): array
    {
        return [
            CategorizableServiceProvider::class,
        ];
    }
	
	
	/**
	 * @param Application $app
	 * @return void
	 */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
	
	/**
	 * @return void
	 */
    protected function setUpDatabase(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create($this->mainTable, static function (Blueprint $table) {
	        $table->increments('id');
	        $table->string('name');
	        $table->string('slug');
	        $table->string('type')->default('default');
	        NestedSet::columns($table);
	        $table->timestamps();
        });
		
        $this->app['db']->connection()->getSchemaBuilder()->create($this->morphTable, static function (Blueprint $table) {
	        $table->integer('category_id');
	        $table->morphs('model');
        });
		
        $this->app['db']->connection()->getSchemaBuilder()->create('posts', static function (Blueprint $table) {
	        $table->increments('id');
	        $table->string('title');
	        $table->string('body');
        });
    }
	
	/**
	 * @return void
	 */
    protected function setUpSoftDeletes(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->table($this->mainTable, function (Blueprint $table) {
            $table->softDeletes();
        });
    }
}
