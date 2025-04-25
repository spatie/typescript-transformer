<?php

namespace Spatie\TypeScriptTransformer\Tests\Transformers;

use DateTime;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\IntBackedEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\StringBackedEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\StringBackedEnumWithSingleQuotes;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use UnitEnum;

beforeEach(function () {
    if (\PHP_VERSION_ID < 80100) {
        test()->markTestSkipped('Native enums not supported before PHP 8.1');
    }
});

it('will only convert enums', function () {
    $transformer = new EnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
    );

    assertNotNull($transformer->transform(
        new ReflectionClass(StringBackedEnum::class),
        'Enum',
    ));

    assertNull($transformer->transform(
        new ReflectionClass(DateTime::class),
        'Enum',
    ));
});

it('does not transform a unit enum', function () {
    $transformer = new EnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
    );

    $type = $transformer->transform(
        new ReflectionClass(UnitEnum::class),
        'Enum'
    );

    assertNull($type);
});

it('can transform a backed enum into enum', function () {
    $transformer = new EnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
    );

    $type = $transformer->transform(
        new ReflectionClass(StringBackedEnum::class),
        'Enum'
    );

    assertEquals("JS = 'js', PHP = 'php', BackslashesTest = 'backslashes\\\\test'", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
    assertFalse($type->isInline);
    assertEquals('enum', $type->keyword);
});

it('can transform a backed enum into a union', function () {
    $transformer = new EnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
    );

    $type = $transformer->transform(
        new ReflectionClass(StringBackedEnum::class),
        'Enum'
    );

    assertEquals("'js' | 'php' | 'backslashes\\\\test'", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
    assertFalse($type->isInline);
    assertEquals('type', $type->keyword);
});

it('can transform a backed enum with integers into an enum', function () {
    $transformer = new EnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
    );

    $type = $transformer->transform(
        new ReflectionClass(IntBackedEnum::class),
        'Enum'
    );

    assertEquals("JS = 1, PHP = 2", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
    assertFalse($type->isInline);
    assertEquals('enum', $type->keyword);
});

it('can transform a backed enum with integers into a union', function () {
    $transformer = new EnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
    );

    $type = $transformer->transform(
        new ReflectionClass(IntBackedEnum::class),
        'Enum'
    );

    assertEquals("1 | 2", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
    assertFalse($type->isInline);
    assertEquals('type', $type->keyword);
});

it('can transform a backed enum with strings with single-quotes into a enum', function () {
    $transformer = new EnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
    );

    $type = $transformer->transform(
        new ReflectionClass(StringBackedEnumWithSingleQuotes::class),
        'Enum'
    );

    assertEquals("'NO_QUOTE' = 'no quote', 'HAS_QUOTE' = 'has quote \''", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
    assertFalse($type->isInline);
    assertEquals('enum', $type->keyword);
});

it('can transform a backed enum with strings with single-quotes into a union', function () {
    $transformer = new EnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
    );

    $type = $transformer->transform(
        new ReflectionClass(StringBackedEnumWithSingleQuotes::class),
        'Enum'
    );

    assertEquals("'no quote' | 'has quote \''", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
    assertFalse($type->isInline);
    assertEquals('type', $type->keyword);
});
