<?php

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\MyclabsEnum;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

it('will only convert enums', function () {
    $transformer = new MyclabsEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class, [])
    );

    assertTrue($transformer->canTransform(
        new ReflectionClass(MyclabsEnum::class),
    ));

    assertFalse($transformer->canTransform(
        new ReflectionClass(DateTime::class),
    ));
});

it('can transform a myclabs enum into an enum', function () {
    $transformer = new MyclabsEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class, ['as_native_enum' => true])
    );

    $type = $transformer->transform(new ReflectionClass(MyclabsEnum::class));

    expect($type)
        ->inline->toBeFalse()
        ->typeReferences->toBeEmpty()
        ->toString()->toBe("enum MyclabsEnum {VIEW = 'view', EDIT = 'edit'}");
});

it('can transform a myclabs enum into a union type', function () {
    $transformer = new MyclabsEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class, ['as_native_enum' => false])
    );

    $type = $transformer->transform(new ReflectionClass(MyclabsEnum::class));

    expect($type)
        ->inline->toBeFalse()
        ->typeReferences->toBeEmpty()
        ->toString()->toBe("type MyclabsEnum = 'view' | 'edit';");
});
