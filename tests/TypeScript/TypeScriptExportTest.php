<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptExport;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can write an export wrapping a named node', function () {
    $node = new TypeScriptExport(
        new TypeScriptAlias(
            new TypeScriptIdentifier('Name'),
            new TypeScriptString(),
        ),
    );

    expect($node->write(new WritingContext([])))->toBe('export type Name = string;');
});
