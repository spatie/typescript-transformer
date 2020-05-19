<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mappers
    |--------------------------------------------------------------------------
    |
    | The path where typescript transformer will look for data structures
    | to transform, this will be the `app` path by default.
    |
    */

    'searching_path' => app_path(),

    /*
    |--------------------------------------------------------------------------
    | Transformers
    |--------------------------------------------------------------------------
    |
    | In these classes you transform your data structures(e.g. enums) to
    | options that can be used in typescript types.
    |
    */

    'transformers' => [
        Spatie\TypescriptTransformer\Transformers\EnumTransformer::class,
        Spatie\TypescriptTransformer\Transformers\StateTransformer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default file
    |--------------------------------------------------------------------------
    |
    | When transforming PHP classes an output file can be given for the class
    | but when left empty the type for the class will be written to this
    | file.
    |
    */

    'default_file' => 'types/generated.d.ts',

    /*
    |--------------------------------------------------------------------------
    | Output path
    |--------------------------------------------------------------------------
    |
    | When writing typescript files they will be written to the following
    | directory, by default this is the `resources/js` directory
    |
    */

    'output_path' => resource_path('js'),
];
