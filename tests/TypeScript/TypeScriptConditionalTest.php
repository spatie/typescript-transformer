<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptConditional;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptOperator;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can write a conditional type', function () {
    $node = new TypeScriptConditional(
        TypeScriptOperator::extends(
            new TypeScriptIdentifier('T'),
            new TypeScriptString(),
        ),
        new TypeScriptNumber(),
        new TypeScriptString(),
    );

    expect($node->write(new WritingContext([])))->toBe('T extends string ? number : string');
});
