<?php


use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\IntBackedEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\StringBackedEnum;
use Spatie\TypeScriptTransformer\Transformers\NativeEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

it('will only convert enums', function () {
    $transformer = new NativeEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(NativeEnumTransformer::class)
    );

    assertTrue($transformer->canTransform(
        new ReflectionClass(StringBackedEnum::class),
    ));

    assertFalse($transformer->canTransform(
        new ReflectionClass(UnitEnum::class),
    ));

    assertFalse($transformer->canTransform(
        new ReflectionClass(DateTime::class),
    ));
});

it('can transform a backed enum into a native enum', function () {
    $transformer = new NativeEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(NativeEnumTransformer::class, ['as_native_enum' => true])
    );

    $type = $transformer->transform(
        new ReflectionClass(StringBackedEnum::class),
    );

    expect($type)
        ->inline->toBeFalse()
        ->typeReferences->toBeEmpty()
        ->toString()->toBe("enum StringBackedEnum {JS = 'js', PHP = 'php'}");
});

it('can transform a backed enum into a native enum with alias', function () {
    $transformer = new NativeEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(NativeEnumTransformer::class, ['as_native_enum' => true])
    );

    $type = $transformer->transform(
        new ReflectionClass(StringBackedEnum::class),
        'Enum'
    );

    expect($type)->toString()->toBe("enum Enum {JS = 'js', PHP = 'php'}");
});

it('can transform a backed enum into a union', function () {
    $transformer = new NativeEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(NativeEnumTransformer::class, ['as_native_enum' => false])
    );

    $type = $transformer->transform(
        new ReflectionClass(StringBackedEnum::class),
    );

    expect($type)
        ->inline->toBeFalse()
        ->typeReferences->toBeEmpty()
        ->toString()->toBe("type StringBackedEnum = 'js' | 'php';");
});

it('can transform a backed enum with integers into an enum', function () {
    $transformer = new NativeEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(NativeEnumTransformer::class, ['as_native_enum' => true])
    );

    $type = $transformer->transform(
        new ReflectionClass(IntBackedEnum::class),
    );

    expect($type)
        ->inline->toBeFalse()
        ->typeReferences->toBeEmpty()
        ->toString()->toBe("enum IntBackedEnum {JS = 1, PHP = 2}");
});

it('can transform a backed enum with integers into a union', function () {
    $transformer = new NativeEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(NativeEnumTransformer::class, ['as_native_enum' => false])
    );

    $type = $transformer->transform(
        new ReflectionClass(IntBackedEnum::class),
    );

    expect($type)
        ->inline->toBeFalse()
        ->typeReferences->toBeEmpty()
        ->toString()->toBe("type IntBackedEnum = 1 | 2;");
});
