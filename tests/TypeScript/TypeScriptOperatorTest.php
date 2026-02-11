<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptOperator;

it('can write keyof', function () {
    $node = TypeScriptOperator::keyof(new TypeScriptIdentifier('User'));

    expect($node->write(new WritingContext([])))->toBe('keyof User');
});

it('can write typeof', function () {
    $node = TypeScriptOperator::typeof(new TypeScriptIdentifier('config'));

    expect($node->write(new WritingContext([])))->toBe('typeof config');
});

it('can write extends', function () {
    $node = TypeScriptOperator::extends(
        new TypeScriptIdentifier('T'),
        new TypeScriptIdentifier('object'),
    );

    expect($node->write(new WritingContext([])))->toBe('T extends object');
});

it('can write as', function () {
    $node = TypeScriptOperator::as(
        new TypeScriptIdentifier('value'),
        new TypeScriptIdentifier('string'),
    );

    expect($node->write(new WritingContext([])))->toBe('value as string');
});

it('can write in', function () {
    $node = TypeScriptOperator::in(
        new TypeScriptIdentifier('K'),
        new TypeScriptIdentifier('T'),
    );

    expect($node->write(new WritingContext([])))->toBe('K in T');
});
