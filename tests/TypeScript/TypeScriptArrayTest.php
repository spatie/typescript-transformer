<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('writes an untyped array as Array<any>', function () {
    $node = new TypeScriptArray([]);

    expect($node->write(new WritingContext([])))->toBe('Array<any>');
});

it('can write a single-type array', function () {
    $node = new TypeScriptArray([new TypeScriptString()]);

    expect($node->write(new WritingContext([])))->toBe('string[]');
});

it('wraps union types in parentheses', function () {
    $node = new TypeScriptArray([
        new TypeScriptString(),
        new TypeScriptNumber(),
    ]);

    expect($node->write(new WritingContext([])))->toBe('(string| number)[]');
});
