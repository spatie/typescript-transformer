<?php

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypeScriptTransformer\Types\StructType;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

it('can create the type from array', function () {
    $struct = StructType::fromArray([
        'a_string' => 'string',
        'a_float' => 'float',
        'a_class' => RegularEnum::class,
        'an_array' => 'int[]',
        'an_object' => [
        'a_bool' => 'bool',
        'an_int' => 'int',
        ],
    ]);

    assertInstanceOf(StructType::class, $struct);
    assertEquals([
        'a_string' => new String_(),
        'a_float' => new Float_(),
        'a_class' => new Object_(new Fqsen('\\'.RegularEnum::class)),
        'an_array' => new Array_(new Integer()),
        'an_object' => new StructType([
        'a_bool' => new Boolean(),
        'an_int' => new Integer(),
        ]),
    ], $struct->getTypes());
});
