<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('writes object keyword when empty', function () {
    $node = new TypeScriptObject([]);

    expect($node->write(new WritingContext([])))->toBe('object');
});

it('can write an object with properties', function () {
    $node = new TypeScriptObject([
        new TypeScriptProperty('name', new TypeScriptString()),
    ]);

    $expected = <<<'TS'
{
name: string
}
TS;

    expect($node->write(new WritingContext([])))->toBe($expected);
});
