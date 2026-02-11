<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;

it('passes through raw TypeScript', function () {
    $node = new TypeScriptRaw('Record<string, never>');

    expect($node->write(new WritingContext([])))->toBe('Record<string, never>');
});
