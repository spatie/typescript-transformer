<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can write a simple type alias', function () {
    $node = new TypeScriptAlias(
        new TypeScriptIdentifier('Name'),
        new TypeScriptString(),
    );

    expect($node->write(new WritingContext([])))->toBe('type Name = string;');
});

it('can write a type alias using a string name', function () {
    $node = new TypeScriptAlias(
        'Name',
        new TypeScriptString(),
    );

    expect($node->write(new WritingContext([])))->toBe('type Name = string;');
});

it('can write a generic type alias', function () {
    $node = new TypeScriptAlias(
        new TypeScriptGeneric(
            new TypeScriptIdentifier('Container'),
            [new TypeScriptIdentifier('T')],
        ),
        new TypeScriptIdentifier('T'),
    );

    expect($node->write(new WritingContext([])))->toBe('type Container<T> = T;');
});
