<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptTuple;

it('can write a tuple', function () {
    $node = new TypeScriptTuple([
        new TypeScriptString(),
        new TypeScriptNumber(),
    ]);

    expect($node->write(new WritingContext([])))->toBe('[string, number]');
});
