<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamespace;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\Visitor\Visitor;

it('can write a declare namespace', function () {
    $node = new TypeScriptNamespace(
        ['App', 'Models'],
        [new TypeScriptRaw('type User = { name: string };')],
    );

    $expected = <<<'TS'
declare namespace App.Models{
type User = { name: string };
}
TS;

    expect($node->write(new WritingContext([])))->toBe($expected);
});

it('can write a non-declare namespace', function () {
    $node = new TypeScriptNamespace(
        ['App', 'Models'],
        [new TypeScriptRaw('type User = { name: string };')],
        declare: false,
    );

    $expected = <<<'TS'
namespace App.Models{
type User = { name: string };
}
TS;

    expect($node->write(new WritingContext([])))->toBe($expected);
});

it('visitor traverses namespace child types', function () {
    $node = new TypeScriptNamespace(
        ['App'],
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
