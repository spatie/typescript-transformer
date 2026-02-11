<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIntersection;

it('can write an intersection type', function () {
    $node = new TypeScriptIntersection([
        new TypeScriptIdentifier('A'),
        new TypeScriptIdentifier('B'),
    ]);

    expect($node->write(new WritingContext([])))->toBe('A & B');
});
