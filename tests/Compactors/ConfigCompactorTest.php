<?php

use Spatie\TypeScriptTransformer\Compactors\ConfigCompactor;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use function PHPUnit\Framework\assertEquals;

it('removes suffix', function () {
    $compactor = new ConfigCompactor(
        (new TypeScriptTransformerConfig())
            ->compactorSuffixes(['Data', 'Dto'])
    );
    assertEquals(
        'Hello\User',
        $compactor->removeSuffix('Hello\UserData')
    );
});
it('removes prefix', function () {
    $compactor = new ConfigCompactor(
        (new TypeScriptTransformerConfig())
            ->compactorPrefixes(['App\Data', 'App\Tests\Data'])
    );
    assertEquals(
        'Product',
        $compactor->removePrefix('App\Data\Product')
    );
    assertEquals(
        'User',
        $compactor->removePrefix('App\Tests\Data\User')
    );
});
