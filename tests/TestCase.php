<?php

namespace AliBayat\LaravelCategorizable\Tests;

use Illuminate\Database\Schema\Blueprint;
use AliBayat\LaravelCategorizable\CategorizableServiceProvide;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            CategorizableServiceProvide::class,
        ];
    }


    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create(config('laravel-categorizable.table_names.main_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('custom_column_sort');
            $table->integer('order_column');
        });

        collect(range(1, 20))->each(function (int $i) {
            Dummy::create([
                'name' => $i,
                'custom_column_sort' => rand(),
            ]);
        });
    }

    protected function setUpSoftDeletes()
    {
        $this->app['db']->connection()->getSchemaBuilder()->table(config('laravel-categorizable.table_names.main_table'), function (Blueprint $table) {
            $table->softDeletes();
        });
    }
}
