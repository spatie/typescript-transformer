<?php

use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptEnum;

it('can write enums in all sorts of configs', function (
    array $cases,
    string $expected,
) {
    $node = new TypeScriptEnum(
        'Enum',
        $cases,
    );

    expect($node->write(new WritingContext(fn () => '')))->toBe($expected);
})->with(function () {
    yield 'numeric enum without indexes' => [
        'cases' => [
            ['name' => 'Up', 'value' => null],
            ['name' => 'Down', 'value' => null],
            ['name' => 'Left', 'value' => null],
            ['name' => 'Right', 'value' => null],
        ],
        'expected' => <<<TS
enum Enum {
    Up,
    Down,
    Left,
    Right,
}
TS
        ,
    ];

    yield 'numeric enum with indexes' => [
        'cases' => [
            ['name' => 'Up', 'value' => null],
            ['name' => 'Down', 'value' => 3],
            ['name' => 'Left', 'value' => null],
            ['name' => 'Right', 'value' => null],
        ],
        'expected' => <<<TS
enum Enum {
    Up,
    Down = 3,
    Left,
    Right,
}
TS
        ,
    ];

    yield 'string enum' => [
        'cases' => [
            ['name' => 'Up', 'value' => 'up'],
            ['name' => 'Down', 'value' => 'down'],
            ['name' => 'Left', 'value' => 'left'],
            ['name' => 'Right', 'value' => 'right'],
        ],
        'expected' => <<<TS
enum Enum {
    Up = 'up',
    Down = 'down',
    Left = 'left',
    Right = 'right',
}
TS
    ,
    ];
});
