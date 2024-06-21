<?php

use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\HiddenAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\IntBackedEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\OptionalAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\ReadonlyClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleInterface;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\TypeScriptAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\TypeScriptLocationAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\UnitEnum;

it('can discover types', function () {
    $types = app(DiscoverTypesAction::class)->execute([
        __DIR__.'/../Fakes/TypesToProvide',
    ]);

    expect($types)->toBe([
        StringBackedEnum::class,
        HiddenAttributedClass::class,
        TypeScriptAttributedClass::class,
        SimpleInterface::class,
        TypeScriptLocationAttributedClass::class,
        OptionalAttributedClass::class,
        ReadonlyClass::class,
        SimpleClass::class,
        UnitEnum::class,
        IntBackedEnum::class,
    ]);
});
