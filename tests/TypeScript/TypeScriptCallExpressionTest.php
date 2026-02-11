<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptCallExpression;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptLiteral;

it('can write a call expression without generics', function () {
    $node = new TypeScriptCallExpression(
        new TypeScriptIdentifier('createAction'),
        [new TypeScriptLiteral('index')],
    );

    expect($node->write(new WritingContext([])))->toBe('createAction("index")');
});

it('can write a call expression with generics', function () {
    $node = new TypeScriptCallExpression(
        new TypeScriptIdentifier('createAction'),
        [new TypeScriptLiteral('index')],
        genericTypes: [new TypeScriptIdentifier('UserParams')],
    );

    expect($node->write(new WritingContext([])))->toBe('createAction<UserParams>("index")');
});

it('can write a call expression with no arguments', function () {
    $node = new TypeScriptCallExpression(
        new TypeScriptIdentifier('getAll'),
    );

    expect($node->write(new WritingContext([])))->toBe('getAll()');
});
