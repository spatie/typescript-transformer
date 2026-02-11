<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObjectLiteral;

it('can write an object literal from a PHP array', function () {
    $node = new TypeScriptObjectLiteral(['method' => 'GET', 'url' => '/users']);

    $expected = <<<'JSON'
{
    "method": "GET",
    "url": "/users"
}
JSON;

    expect($node->write(new WritingContext([])))->toBe($expected);
});
