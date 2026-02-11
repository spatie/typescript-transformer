<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;

it('can write a union type', function () {
    $node = new TypeScriptUnion([
        new TypeScriptString(),
        new TypeScriptNumber(),
    ]);

    expect($node->write(new WritingContext([])))->toBe('string | number');
});

it('deduplicates types', function () {
    $node = new TypeScriptUnion([
        new TypeScriptString(),
        new TypeScriptString(),
        new TypeScriptNumber(),
    ]);

    expect($node->write(new WritingContext([])))->toBe('string | number');
});
