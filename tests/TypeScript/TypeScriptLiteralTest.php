<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptLiteral;

it('can write literal values', function (int|string|float|bool|null $value, string $expected) {
    $node = new TypeScriptLiteral($value);

    expect($node->write(new WritingContext([])))->toBe($expected);
})->with(function () {
    yield 'string' => ['hello', "'hello'"];
    yield 'int' => [42, '42'];
    yield 'negative int' => [-7, '-7'];
    yield 'float' => [3.14, '3.14'];
    yield 'true' => [true, 'true'];
    yield 'false' => [false, 'false'];
    yield 'null' => [null, 'null'];
    yield 'string with backslash' => ['App\\Models\\User', "'App\\\\Models\\\\User'"];
    yield 'string with single quote' => ["it's", "'it\\'s'"];
    yield 'string with newline' => ["line1\nline2", "'line1\\nline2'"];
    yield 'string with carriage return' => ["a\rb", "'a\\rb'"];
    yield 'string with tab' => ["a\tb", "'a\\tb'"];
    yield 'string with forward slash' => ['image/png', "'image/png'"];
    yield 'string with mixed escapes' => ["a\\b'c\nd", "'a\\\\b\\'c\\nd'"];
});
