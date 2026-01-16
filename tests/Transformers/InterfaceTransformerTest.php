<?php

use function Spatie\Snapshots\assertMatchesSnapshot;

use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleInterface;
use Spatie\TypeScriptTransformer\Tests\Support\AllInterfaceTransformer;

it('transforms methods in interfaces', function () {
    $transformed = classesToTypeScript([SimpleInterface::class], new AllInterfaceTransformer());

    assertMatchesSnapshot($transformed);
});
