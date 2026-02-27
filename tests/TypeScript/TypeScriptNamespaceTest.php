<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamespace;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptOperator;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\Visitor\Visitor;

it('can write a namespace', function () {
    $node = new TypeScriptNamespace(
        'App',
        [new TypeScriptRaw('type User = { name: string };')],
    );

    $expected = <<<'TS'
namespace App {
type User = { name: string };
}
TS;

    expect($node->write(new WritingContext([])))->toBe($expected);
});

it('can write a declare namespace using operator', function () {
    $node = TypeScriptOperator::declare(
        new TypeScriptNamespace(
            'App',
            [new TypeScriptRaw('type User = { name: string };')],
        ),
    );

    $expected = <<<'TS'
declare namespace App {
type User = { name: string };
}
TS;

    expect($node->write(new WritingContext([])))->toBe($expected);
});

it('can write nested namespaces', function () {
    $node = TypeScriptOperator::declare(
        new TypeScriptNamespace(
            'level1',
            [new TypeScriptRaw('export type Level1Type = string;')],
            children: [
                new TypeScriptNamespace(
                    'level2',
                    [new TypeScriptRaw('export type Level2Type = string;')],
                ),
            ],
        ),
    );

    $expected = <<<'TS'
declare namespace level1 {
export type Level1Type = string;
namespace level2 {
export type Level2Type = string;
}
}
TS;

    expect($node->write(new WritingContext([])))->toBe($expected);
});

it('visitor traverses namespace child types', function () {
    $node = new TypeScriptNamespace(
        'App',
        [$stringNode = new TypeScriptString()],
    );

    $visited = [];

    Visitor::create()
        ->before(function (TypeScriptNode $node) use (&$visited) {
            $visited[] = $node::class;
        })
        ->execute($node);

    expect($visited)->toContain(TypeScriptString::class);
});

it('visitor traverses namespace children', function () {
    $childNamespace = new TypeScriptNamespace(
        'Models',
        [new TypeScriptString()],
    );

    $node = new TypeScriptNamespace(
        'App',
        [],
        children: [$childNamespace],
    );

    $visited = [];

    Visitor::create()
        ->before(function (TypeScriptNode $node) use (&$visited) {
            $visited[] = $node::class;
        })
        ->execute($node);

    expect($visited)->toContain(TypeScriptNamespace::class);
    expect($visited)->toContain(TypeScriptString::class);
});
