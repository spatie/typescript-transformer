<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptLiteral;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptVariableDeclaration;

it('can write a const declaration', function () {
    $node = TypeScriptVariableDeclaration::const('name', new TypeScriptLiteral('world'));

    expect($node->write(new WritingContext([])))->toBe('const name = "world"');
});

it('can write a let declaration', function () {
    $node = TypeScriptVariableDeclaration::let('count', new TypeScriptLiteral(0));

    expect($node->write(new WritingContext([])))->toBe('let count = 0');
});

it('can write a var declaration', function () {
    $node = TypeScriptVariableDeclaration::var('legacy', new TypeScriptLiteral(true));

    expect($node->write(new WritingContext([])))->toBe('var legacy = true');
});

it('can write a declaration with type annotation', function () {
    $node = TypeScriptVariableDeclaration::const(
        'name',
        new TypeScriptLiteral('world'),
        type: new TypeScriptString(),
    );

    expect($node->write(new WritingContext([])))->toBe('const name: string = "world"');
});
