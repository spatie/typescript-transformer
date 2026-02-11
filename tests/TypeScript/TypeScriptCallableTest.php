<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptCallable;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptParameter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptVoid;

it('can write a callable type', function () {
    $node = new TypeScriptCallable();

    expect($node->write(new WritingContext([])))->toBe('(...args: any[]) => any');
});

it('can write a callable with custom parameters and return type', function () {
    $node = new TypeScriptCallable(
        [new TypeScriptParameter('x', new TypeScriptString())],
        new TypeScriptVoid(),
    );

    expect($node->write(new WritingContext([])))->toBe('(x: string) => void');
});

it('can write a callable with no parameters and a return type', function () {
    $node = new TypeScriptCallable(
        [],
        new TypeScriptNumber(),
    );

    expect($node->write(new WritingContext([])))->toBe('() => number');
});

it('can write a callable with parameters and default return type', function () {
    $node = new TypeScriptCallable(
        [
            new TypeScriptParameter('a', new TypeScriptString()),
            new TypeScriptParameter('b', new TypeScriptNumber()),
        ],
    );

    expect($node->write(new WritingContext([])))->toBe('(a: string, b: number) => any');
});
