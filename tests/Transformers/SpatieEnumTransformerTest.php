<?php

use Spatie\TypeScriptTransformer\Tests\FakeClasses\MyclabsEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\SpatieEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\States\ChildState;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\States\State;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\StringBackedEnum;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\Transformers\NativeEnumTransformer;
use Spatie\TypeScriptTransformer\Transformers\SpatieEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

it('will only convert enums', function () {
    $transformer = new SpatieEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(SpatieEnumTransformer::class, [])
    );

    assertTrue($transformer->canTransform(
        new ReflectionClass(SpatieEnum::class),
    ));

    assertFalse($transformer->canTransform(
        new ReflectionClass(DateTime::class),
    ));
});

it('can transform a Spatie enum into an enum', function () {
    $transformer = new SpatieEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(SpatieEnumTransformer::class, ['as_native_enum' => true])
    );

    $type = $transformer->transform(
        new ReflectionClass(SpatieEnum::class),
    );

    expect($type)
        ->inline->toBeFalse()
        ->typeReferences->toBeEmpty()
        ->toString()->toBe("enum SpatieEnum {Draft = 'draft', Published = 'published', Archived = 'archived'}");
});

it('can transform a Spatie enum into a union type', function () {
    $transformer = new SpatieEnumTransformer(
        TypeScriptTransformerConfig::create()->transformer(SpatieEnumTransformer::class, ['as_native_enum' => false])
    );

    $type = $transformer->transform(
        new ReflectionClass(SpatieEnum::class),
    );

    expect($type)
        ->inline->toBeFalse()
        ->typeReferences->toBeEmpty()
        ->toString()->toBe("type SpatieEnum = 'draft' | 'published' | 'archived';");
});
