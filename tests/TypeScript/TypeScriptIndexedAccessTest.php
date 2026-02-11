<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIndexedAccess;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptLiteral;

it('can write an indexed access with a single segment', function () {
    $node = new TypeScriptIndexedAccess(
        new TypeScriptIdentifier('User'),
        [new TypeScriptLiteral('name')],
    );

    expect($node->write(new WritingContext([])))->toBe('User["name"]');
});

it('can write an indexed access with multiple segments', function () {
    $node = new TypeScriptIndexedAccess(
        new TypeScriptIdentifier('User'),
        [new TypeScriptLiteral('address'), new TypeScriptLiteral('city')],
    );

    expect($node->write(new WritingContext([])))->toBe('User["address"]["city"]');
});
