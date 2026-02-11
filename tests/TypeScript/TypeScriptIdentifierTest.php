<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;

it('can write a plain identifier', function () {
    $node = new TypeScriptIdentifier('MyType');

    expect($node->write(new WritingContext([])))->toBe('MyType');
});

it('can write an identifier that needs quoting', function () {
    $node = new TypeScriptIdentifier('my-type');

    expect($node->write(new WritingContext([])))->toBe("'my-type'");
});

it('returns the name', function () {
    $node = new TypeScriptIdentifier('Foo');

    expect($node->getName())->toBe('Foo');
});
