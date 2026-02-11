<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can write a plain property', function () {
    $node = new TypeScriptProperty('name', new TypeScriptString());

    expect($node->write(new WritingContext([])))->toBe('name: string');
});

it('can write an optional property', function () {
    $node = new TypeScriptProperty('name', new TypeScriptString(), isOptional: true);

    expect($node->write(new WritingContext([])))->toBe('name?: string');
});

it('can write a readonly property', function () {
    $node = new TypeScriptProperty('name', new TypeScriptString(), isReadonly: true);

    expect($node->write(new WritingContext([])))->toBe('readonly name: string');
});
