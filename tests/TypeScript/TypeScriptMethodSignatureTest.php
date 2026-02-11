<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptMethodSignature;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptParameter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can write a method signature', function () {
    $node = new TypeScriptMethodSignature(
        'getName',
        [new TypeScriptParameter('id', new TypeScriptNumber())],
        new TypeScriptString(),
    );

    expect($node->write(new WritingContext([])))->toBe('getName(id: number): string;');
});

it('accepts a string name and converts it to an identifier', function () {
    $node = new TypeScriptMethodSignature(
        'doSomething',
        [],
        new TypeScriptString(),
    );

    expect($node->write(new WritingContext([])))->toBe('doSomething(): string;');
});
