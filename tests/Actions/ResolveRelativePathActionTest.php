<?php

use Spatie\TypeScriptTransformer\Actions\ResolveRelativePathAction;

it('will return the correct path', function (string $current, string $requested, ?string $expected) {
    expect((new ResolveRelativePathAction())->execute(
        $current,
        $requested
    ))->toBe($expected);
})->with(
    [
        // Same file
        [
            'c.ts',
            'c.ts',
            null,
        ],
        [
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'c.ts']),
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'c.ts']),
            null,
        ],
        // Nesting - last segment is filename, not directory
        [
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'c.ts']),
            implode(DIRECTORY_SEPARATOR, ['a', 'd', 'e.ts']),
            '../d/e',
        ],
        [
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'c', 'd.ts']),
            implode(DIRECTORY_SEPARATOR, ['a', 'd', 'e.ts']),
            '../../d/e',
        ],
        [
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'c.ts']),
            implode(DIRECTORY_SEPARATOR, ['a', 'd', 'e', 'f.ts']),
            '../d/e/f',
        ],
        [
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'c', 'd.ts']),
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'e', 'd.ts']),
            '../e/d',
        ],
        [
            implode(DIRECTORY_SEPARATOR, ['a', 'b.ts']),
            implode(DIRECTORY_SEPARATOR, ['a', 'index.ts']),
            null,
        ],
        // Index files - will not appear in path
        [
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'index.ts']),
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'index.ts']),
            null,
        ],
        [
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'index.ts']),
            implode(DIRECTORY_SEPARATOR, ['a', 'd', 'index.ts']),
            '../d',
        ],
        [
            implode(DIRECTORY_SEPARATOR, ['a', 'b', 'index.ts']),
            implode(DIRECTORY_SEPARATOR, ['a', 'd', 'e.ts']),
            '../d/e',
        ],
        // Same level in same directory
        [
            'a.ts',
            'b.ts',
            './b',
        ],
        [
            implode(DIRECTORY_SEPARATOR, ['a', 'b.ts']),
            implode(DIRECTORY_SEPARATOR, ['a', 'c.ts']),
            './c',
        ],
    ]
);
