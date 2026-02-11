<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArrayExpression;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptLiteral;

it('can write an empty array expression', function () {
    $node = new TypeScriptArrayExpression();

    expect($node->write(new WritingContext([])))->toBe('[]');
});

it('can write an array expression with elements', function () {
    $node = new TypeScriptArrayExpression([
        new TypeScriptLiteral('a'),
        new TypeScriptLiteral('b'),
        new TypeScriptLiteral('c'),
    ]);

    expect($node->write(new WritingContext([])))->toBe('["a", "b", "c"]');
});
