<?php

use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpEnumNode;
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

    expect($types)->toEqual([
        new PhpEnumNode(new ReflectionEnum(StringBackedEnum::class)),
        new PhpClassNode(new ReflectionClass(HiddenAttributedClass::class)),
        new PhpClassNode(new ReflectionClass(TypeScriptAttributedClass::class)),
        new PhpClassNode(new ReflectionClass(SimpleInterface::class)),
        new PhpClassNode(new ReflectionClass(TypeScriptLocationAttributedClass::class)),
        new PhpClassNode(new ReflectionClass(OptionalAttributedClass::class)),
        new PhpClassNode(new ReflectionClass(ReadonlyClass::class)),
        new PhpClassNode(new ReflectionClass(SimpleClass::class)),
        new PhpEnumNode(new ReflectionEnum(UnitEnum::class)),
        new PhpEnumNode(new ReflectionEnum(IntBackedEnum::class)),
    ]);
});
