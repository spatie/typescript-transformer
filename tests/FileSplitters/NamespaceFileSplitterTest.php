<?php

use Spatie\TypeScriptTransformer\Actions\ResolveTypesCollectionAction;
use Spatie\TypeScriptTransformer\FileSplitters\NamespaceFileSplitter;
use Spatie\TypeScriptTransformer\Structures\SplitTypesCollection;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\NativeEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

it('can split a types collection to one single file', function () {
    $typesCollection = (new ResolveTypesCollectionAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
            ->transformer(NativeEnumTransformer::class)
            ->transformer(DtoTransformer::class)
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
