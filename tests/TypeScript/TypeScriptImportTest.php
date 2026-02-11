<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptImport;

it('can write an import', function () {
    $node = new TypeScriptImport('./types', [
        ['name' => 'User'],
    ]);

    expect($node->write(new WritingContext([])))->toBe("import { User } from './types';");
});

it('can write an import with aliases', function () {
    $node = new TypeScriptImport('./types', [
        ['name' => 'User', 'alias' => 'AppUser'],
        ['name' => 'Role'],
    ]);

    expect($node->write(new WritingContext([])))->toBe("import { User as AppUser, Role } from './types';");
});
