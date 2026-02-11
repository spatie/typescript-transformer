<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptFunctionDeclaration;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptParameter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can write a function declaration using a string name', function () {
    $node = new TypeScriptFunctionDeclaration(
        'greet',
        [new TypeScriptParameter('name', new TypeScriptString())],
        new TypeScriptString(),
        new TypeScriptRaw('return `Hello ${name}`;'),
    );

    $expected = <<<'TS'
function greet(name: string): string {
return `Hello ${name}`;
}
TS;

    expect($node->write(new WritingContext([])))->toBe($expected);
});

it('can write a function declaration', function () {
    $node = new TypeScriptFunctionDeclaration(
        new TypeScriptIdentifier('greet'),
        [new TypeScriptParameter('name', new TypeScriptString())],
        new TypeScriptString(),
        new TypeScriptRaw('return `Hello ${name}`;'),
    );

    $expected = <<<'TS'
function greet(name: string): string {
return `Hello ${name}`;
}
TS;

    expect($node->write(new WritingContext([])))->toBe($expected);
});
