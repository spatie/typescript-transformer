<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIndexSignature;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can write an index signature with default name', function () {
    $node = new TypeScriptIndexSignature(new TypeScriptString());

    expect($node->write(new WritingContext([])))->toBe('[index: string]');
});

it('can write an index signature with custom name', function () {
    $node = new TypeScriptIndexSignature(new TypeScriptString(), 'key');

    expect($node->write(new WritingContext([])))->toBe('[key: string]');
});
