<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptLiteral;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptParameter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can write a plain parameter', function () {
    $node = new TypeScriptParameter('name', new TypeScriptString());

    expect($node->write(new WritingContext([])))->toBe('name: string');
});

it('can write an optional parameter', function () {
    $node = new TypeScriptParameter('name', new TypeScriptString(), isOptional: true);

    expect($node->write(new WritingContext([])))->toBe('name?: string');
});

it('can write a spread parameter', function () {
    $node = new TypeScriptParameter('args', new TypeScriptArray([new TypeScriptString()]), isSpread: true);

    expect($node->write(new WritingContext([])))->toBe('...args: string[]');
});

it('can write a parameter with default value', function () {
    $node = new TypeScriptParameter(
        'name',
        new TypeScriptString(),
        defaultValue: new TypeScriptLiteral('world'),
    );

    expect($node->write(new WritingContext([])))->toBe('name: string = "world"');
});
