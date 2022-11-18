<?php

use function Spatie\Snapshots\assertMatchesSnapshot;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;

function getTransformerConfig(): TypeScriptTransformerConfig
{
    return TypeScriptTransformerConfig::create()
        ->autoDiscoverTypes(__DIR__ . '/FakeClasses/Integration')
        ->defaultTypeReplacements([
            DateTime::class => 'string',
        ])
        ->transformers([
            MyclabsEnumTransformer::class,
            DtoTransformer::class,
        ])
        ->collectors([
            DefaultCollector::class,
        ]);
}

it('works', function () {
    $temporaryDirectory = (new TemporaryDirectory())->create();

    $transformer = new TypeScriptTransformer(
        getTransformerConfig()
        ->outputFile($temporaryDirectory->path('types.d.ts'))
    );

    $transformer->transform();

    $transformed = file_get_contents($temporaryDirectory->path('types.d.ts'));

    assertMatchesSnapshot($transformed);
});

it('can transform to es modules', function () {
    $temporaryDirectory = (new TemporaryDirectory())->create();

    $transformer = new TypeScriptTransformer(
        getTransformerConfig()
        ->writer(ModuleWriter::class)
        ->outputFile($temporaryDirectory->path('types.ts'))
    );

    $transformer->transform();

    $transformed = file_get_contents($temporaryDirectory->path('types.ts'));

    assertMatchesSnapshot($transformed);
});
