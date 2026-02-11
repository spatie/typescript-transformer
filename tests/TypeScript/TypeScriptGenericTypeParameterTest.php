<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGenericTypeParameter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnknown;

it('can write a bare type parameter', function () {
    $node = new TypeScriptGenericTypeParameter(new TypeScriptIdentifier('T'));

    expect($node->write(new WritingContext([])))->toBe('T');
});

it('can write a type parameter with extends constraint', function () {
    $node = new TypeScriptGenericTypeParameter(
        new TypeScriptIdentifier('T'),
        extends: new TypeScriptString(),
    );

    expect($node->write(new WritingContext([])))->toBe('T extends string');
});

it('can write a type parameter with default', function () {
    $node = new TypeScriptGenericTypeParameter(
        new TypeScriptIdentifier('T'),
        default: new TypeScriptString(),
    );

    expect($node->write(new WritingContext([])))->toBe('T = string');
});

it('can write a type parameter with extends and default', function () {
    $node = new TypeScriptGenericTypeParameter(
        new TypeScriptIdentifier('T'),
        extends: new TypeScriptIdentifier('object'),
        default: new TypeScriptGeneric(
            new TypeScriptIdentifier('Record'),
            [new TypeScriptString(), new TypeScriptUnknown()],
        ),
    );

    expect($node->write(new WritingContext([])))->toBe('T extends object = Record<string, unknown>');
});
