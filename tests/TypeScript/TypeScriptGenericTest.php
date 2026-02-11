<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can write a generic with single type argument', function () {
    $node = new TypeScriptGeneric(
        new TypeScriptIdentifier('Array'),
        [new TypeScriptString()],
    );

    expect($node->write(new WritingContext([])))->toBe('Array<string>');
});

it('can write a generic with multiple type arguments', function () {
    $node = new TypeScriptGeneric(
        new TypeScriptIdentifier('Record'),
        [new TypeScriptString(), new TypeScriptNumber()],
    );

    expect($node->write(new WritingContext([])))->toBe('Record<string, number>');
});
