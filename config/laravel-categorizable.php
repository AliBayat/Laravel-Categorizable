<?php

declare(strict_types=1);

/**
 * Laravel Categorizable Package by Ali Bayat.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Eloquent Models
    |--------------------------------------------------------------------------
    */

    'models' => [

        /*
        |--------------------------------------------------------------------------
        | Package's Category Model
        |--------------------------------------------------------------------------
        */

        'category' => \AliBayat\LaravelCategorizable\Category::class,

    ],

    'table_names' => [

        /*
        |--------------------------------------------------------------------------
        | Package's tables
        |--------------------------------------------------------------------------
        */

        /*
         * When using the "Categorizable" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value, but you may easily change it to any other table.
         */
        'main_table'  => env('CATEGORIES_MAIN_TABLE', 'categories'),

        /*
         * When using the "Categorizable" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value, but you may easily change it to any other table.
         */
        'morph_table' => env('CATEGORIES_MORPH_TABLE', 'categories_models'),

    ],

];
