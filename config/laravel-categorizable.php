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
         * default value but you may easily change it to any table you like.
         */
        'main_table'  => 'categories',

        /*
         * When using the "Categorizable" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */
        'morph_table' => 'categories_models',

    ],

];
