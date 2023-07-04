<?php


use Spatie\TypeScriptTransformer\Actions\ResolveRelativePathAction;

it('will return the correct path', function (array $current, array $requested, ?string $expected) {
    expect((new ResolveRelativePathAction())->execute(
        $current,
        $requested
    ))->toBe($expected);
})->with(
    [
        [
            [],
            [],
            null,
        ],
        [
            ['a', 'b', 'c'],
            ['a', 'b', 'c'],
            null,
        ],
        [
            ['a', 'b', 'c'],
            ['a', 'd', 'e'],
            './../../d/e',
        ],
        [
            ['a', 'b', 'c', 'd'],
            ['a', 'd', 'e'],
            './../../../d/e',
        ],
        [
            ['a', 'b', 'c'],
            ['a', 'd', 'e', 'f'],
            './../../d/e/f',
        ],
        [
            ['a'],
            ['b'],
            './../b',
        ],
    ]
);
