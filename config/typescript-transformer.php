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
    | Mappers
    |--------------------------------------------------------------------------
    |
    | In these classes you transform your data structures(e.g. enums) to
    | options that can be used in typescript types.
    |
    */

    'mappers' => [
        Spatie\TypescriptTransformer\Mappers\EnumMapper::class,
        Spatie\TypescriptTransformer\Mappers\StateMapper::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default writer
    |--------------------------------------------------------------------------
    |
    | Writers define how your PHP structure will be converted to typescript,
    | you can explicitly define a writer in your PHP classes but by default
    | the following writer will be used:
    |
    */

    'default_writer' => Spatie\TypescriptTransformer\Writers\OptionsWriter::class,

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
