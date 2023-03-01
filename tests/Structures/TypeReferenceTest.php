<?php

use Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\Structures\TypeReference;

it('can create a type reference from fqcn', function (){
    $typeReference = TypeReference::fromFqcn(TypeScriptTransformer::class);

    expect($typeReference)
        ->name->toBe('TypeScriptTransformer')
        ->namespaceSegments->toBe([
            'Spatie',
            'TypeScriptTransformer',
            'Attributes'
        ]);
});

it('can create a type reference from small fqcn', function (){
    $typeReference = TypeReference::fromFqcn(DateTime::class);

    expect($typeReference)
        ->name->toBe('DateTime')
        ->namespaceSegments->toBe([]);
});

it('can get a replace symbol from a type reference', function (){
    $typeReference = TypeReference::fromFqcn(TypeScriptTransformer::class);

    expect($typeReference->replaceSymbol())->toBe(
        "{%Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformer%}"
    );
});

it('can get the fqcn from a type reference', function (){
    $typeReference = TypeReference::fromFqcn(TypeScriptTransformer::class);

    expect($typeReference->getFqcn())->toBe(
        "Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformer"
    );
});
