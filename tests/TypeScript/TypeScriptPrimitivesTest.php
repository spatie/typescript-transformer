<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAny;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNever;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNull;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUndefined;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnknown;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptVoid;

it('can write primitive types', function (object $node, string $expected) {
    expect($node->write(new WritingContext([])))->toBe($expected);
})->with(function () {
    yield 'string' => [new TypeScriptString(), 'string'];
    yield 'number' => [new TypeScriptNumber(), 'number'];
    yield 'boolean' => [new TypeScriptBoolean(), 'boolean'];
    yield 'null' => [new TypeScriptNull(), 'null'];
    yield 'undefined' => [new TypeScriptUndefined(), 'undefined'];
    yield 'void' => [new TypeScriptVoid(), 'void'];
    yield 'never' => [new TypeScriptNever(), 'never'];
    yield 'unknown' => [new TypeScriptUnknown(), 'unknown'];
    yield 'any' => [new TypeScriptAny(), 'any'];
});
