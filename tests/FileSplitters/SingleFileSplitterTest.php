<?php

namespace Spatie\TypeScriptTransformer\Tests\FileSplitters;

use Spatie\TypeScriptTransformer\Actions\ResolveTypesCollectionAction;
use Spatie\TypeScriptTransformer\FileSplitters\SingleFileSplitter;
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

    $splits = (new SingleFileSplitter(['filename' => 'types.d.ts']))->split(
        '/some/path',
        $typesCollection
    );

    expect($splits)
        ->toHaveCount(1)
        ->toEqual([
            new SplitTypesCollection(
                '/some/path/types.d.ts',
                $typesCollection,
                []
            ),
        ]);
});
