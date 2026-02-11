<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptLiteral;

it('can write literal values', function (int|string|bool $value, string $expected) {
    $node = new TypeScriptLiteral($value);

    expect($node->write(new WritingContext([])))->toBe($expected);
})->with(function () {
    yield 'string' => ['hello', '"hello"'];
    yield 'int' => [42, '42'];
    yield 'true' => [true, 'true'];
    yield 'false' => [false, 'false'];
});
