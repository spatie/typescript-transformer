<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptInterface;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptMethodSignature;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptParameter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptVoid;

it('can write an interface using a string name', function () {
    $node = new TypeScriptInterface(
        'User',
        [new TypeScriptProperty('name', new TypeScriptString())],
        [],
    );

    $expected = <<<'TS'
interface User {
name: string
}
TS;

    expect($node->write(new WritingContext([])))->toBe($expected);
});

it('can write an interface with properties and methods', function () {
    $node = new TypeScriptInterface(
        new TypeScriptIdentifier('User'),
        [
            new TypeScriptProperty('name', new TypeScriptString()),
        ],
        [
            new TypeScriptMethodSignature(
                'greet',
                [new TypeScriptParameter('message', new TypeScriptString())],
                new TypeScriptVoid(),
            ),
        ],
    );

    $expected = <<<'TS'
interface User {
name: string
greet(message: string): void;
}
TS;

    expect($node->write(new WritingContext([])))->toBe($expected);
});
