<?php

use phpDocumentor\Reflection\Type;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypeScriptTransformer\Types\StructType;

it('can create the attribute from string', function () {
    $attribute = new TypeScriptType('string|int');

    assertInstanceOf(Type::class, $attribute->getType());
    assertEquals('string|int', (string) $attribute->getType());
});

it('can create the attribute from an array', function () {
    $attribute = new TypeScriptType([
        'a_string' => 'string',
        'a_float' => 'float',
        'a_class' => RegularEnum::class,
        'an_array' => 'int[]',
        'an_object' => [
        'a_bool' => 'bool',
        'an_int' => 'int',
        ],
    ]);

    assertInstanceOf(StructType::class, $attribute->getType());
});
