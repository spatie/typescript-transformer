<?php

use Spatie\TypeScriptTransformer\Actions\ResolveTypesCollectionAction;
use Spatie\TypeScriptTransformer\FileSplitters\NamespaceFileSplitter;
use Spatie\TypeScriptTransformer\FileSplitters\SingleFileSplitter;
use Spatie\TypeScriptTransformer\Structures\SplitTypesCollection;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;
use Spatie\TypeScriptTransformer\Writers\TypeDefinitionWriter;
use Symfony\Component\Finder\Finder;

it('can split a types collection to one single file', function () {
    $typesCollection = (new ResolveTypesCollectionAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
            ->transformers([
                EnumTransformer::class,
                DtoTransformer::class,
            ])
            ->autoDiscoverTypes(__DIR__ . '/../FakeClasses'),
    ))->execute();

    $splits = (new NamespaceFileSplitter([]))->split(
        '/some/path/',
        $typesCollection
    );

    ray($splits);

    expect($splits)
        ->toHaveCount(2)
        ->toEqual([
            new SplitTypesCollection(
                '/some/path/types.d.ts',
                $typesCollection,
                []
            ),
        ]);
});
