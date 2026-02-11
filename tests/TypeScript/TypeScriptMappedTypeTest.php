<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIndexedAccess;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptMappedType;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptOperator;

it('can write a basic mapped type', function () {
    $node = new TypeScriptMappedType(
        'K',
        TypeScriptOperator::keyof(new TypeScriptIdentifier('T')),
        new TypeScriptIndexedAccess(
            new TypeScriptIdentifier('T'),
            [new TypeScriptIdentifier('K')],
        ),
    );

    expect($node->write(new WritingContext([])))->toBe('{ [K in keyof T]: T[K] }');
});

it('can write a mapped type with modifiers', function () {
    $node = new TypeScriptMappedType(
        'K',
        TypeScriptOperator::keyof(new TypeScriptIdentifier('T')),
        new TypeScriptIndexedAccess(
            new TypeScriptIdentifier('T'),
            [new TypeScriptIdentifier('K')],
        ),
        readonlyModifier: 'readonly',
        optionalModifier: '?',
    );

    expect($node->write(new WritingContext([])))->toBe('{ readonly [K in keyof T]?: T[K] }');
});

it('can write a mapped type with as clause', function () {
    $node = new TypeScriptMappedType(
        'K',
        TypeScriptOperator::keyof(new TypeScriptIdentifier('T')),
        new TypeScriptIndexedAccess(
            new TypeScriptIdentifier('T'),
            [new TypeScriptIdentifier('K')],
        ),
        nameType: new TypeScriptIdentifier('NewKey'),
    );

    expect($node->write(new WritingContext([])))->toBe('{ [K in keyof T as NewKey]: T[K] }');
});
