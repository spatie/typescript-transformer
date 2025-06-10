<?php

use Spatie\TypeScriptTransformer\Structures\NamespacedType;
use function PHPUnit\Framework\assertEquals;

it('can find common prefix', function () {
    assertEquals(
        '\Spatie\TypeScriptTransformer\Tests\FakeClasses\\',
        NamespacedType::commonPrefix(
            '\Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\\',
            '\Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections\\'
        )
    );
});
it('common prefix of equal strings is that string', function () {
    assertEquals(
        '\Spatie\TypeScriptTransformer\Tests\FakeClasses\\',
        NamespacedType::commonPrefix(
            '\Spatie\TypeScriptTransformer\Tests\FakeClasses\\',
            '\Spatie\TypeScriptTransformer\Tests\FakeClasses\\'
        )
    );
});
it('common prefix goes up to last backslash even with another nesting level', function () {
    assertEquals(
        'app\\', // not 'app\\co'
        NamespacedType::commonPrefix(
            'app\\companies',
            'app\\countries'
        )
    );
    assertEquals(
        'app\\',
        NamespacedType::commonPrefix(
            'app\\data',
            'app\\data\\stuff'
        )
    );
});
