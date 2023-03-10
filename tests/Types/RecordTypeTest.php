<?php

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\BackedEnumWithoutAnnotation;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;

use Spatie\TypeScriptTransformer\Types\RecordType;
use Spatie\TypeScriptTransformer\Types\StructType;

it('creates a scalar key and an object value', function () {
    $record = new RecordType('string', RegularEnum::class);

    assertInstanceOf(RecordType::class, $record);
    assertEquals(new String_(), $record->getKeyType());
    assertEquals(new Object_(new Fqsen('\\'.RegularEnum::class)), $record->getValueType());
});

it('creates a scalar key and an struct value', function () {
    $record = new RecordType('string', [
        'enum' => RegularEnum::class,
        'array' => 'int[]',
    ]);

    assertInstanceOf(RecordType::class, $record);
    assertEquals(new String_(), $record->getKeyType());

    assertInstanceOf(StructType::class, $record->getValueType());
    assertEquals([
        'enum' => new Object_(new Fqsen('\\'.RegularEnum::class)),
        'array' => new Array_(new Integer()),
    ], $record->getValueType()->getTypes());
});

it('creates a scalar key and an array value', function () {
    $record = new RecordType(RegularEnum::class, BackedEnumWithoutAnnotation::class, array: true);

    assertInstanceOf(RecordType::class, $record);
    assertEquals(new Object_(new Fqsen('\\'.RegularEnum::class)), $record->getKeyType());
    assertEquals(new Array_(new Object_(new Fqsen('\\'.BackedEnumWithoutAnnotation::class))), $record->getValueType());
});
