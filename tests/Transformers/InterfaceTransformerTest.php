<?php

use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleInterface;
use Spatie\TypeScriptTransformer\Tests\TestSupport\AllInterfaceTransformer;

it('transforms methods in interfaces', function () {
    $transformed = classesToTypeScript([SimpleInterface::class], new AllInterfaceTransformer());

    expect($transformed)->toMatchSnapshot();
});
