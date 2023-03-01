<?php

use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\SpatieEnum;
use Spatie\TypeScriptTransformer\Transformers\SpatieEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

it('will only convert enums', function () {
    $transformer = new SpatieEnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
    );

    assertNotNull($transformer->transform(
        new ReflectionClass(SpatieEnum::class),
        'State',
    ));

    assertNull($transformer->transform(
        new ReflectionClass(DateTime::class),
        'State',
    ));
});

it('can transform an enum into a type', function () {
    $transformer = new SpatieEnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
    );

    $type = $transformer->transform(
        new ReflectionClass(SpatieEnum::class),
        'FakeEnum'
    );

    assertEquals("'draft' | 'published' | 'archived'", $type->transformed);
    assertEmpty($type->typeReferences);
    assertFalse($type->isInline);
    assertEquals('type', $type->keyword);
});

it('can transform an enum into an enum', function () {
    $transformer = new SpatieEnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
    );

    $type = $transformer->transform(
        new ReflectionClass(SpatieEnum::class),
        'FakeEnum'
    );

    assertEquals("'draft' = 'Draft', 'published' = 'Published', 'archived' = 'Archived'", $type->transformed);
    assertEmpty($type->typeReferences);
    assertFalse($type->isInline);
    assertEquals('enum', $type->keyword);
});
