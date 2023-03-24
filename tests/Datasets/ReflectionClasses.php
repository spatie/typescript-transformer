<?php

use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithAlreadyTransformedAttributeAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptInlineAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptNamedAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptTransformerAttribute;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionClass;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Types\StructType;

dataset('reflection_classes', function () {
    yield 'non transformable' => [
        'reflection' => $reflection = FakeReflectionClass::create(),
        'transformable' => false,
        'inline' => false,
        'name' => null,
        'transformer' => null,
        'type' => null,
    ];

    yield '@typescript annotation' => [
        'reflection' => $reflection = FakeReflectionClass::create()->withDocComment('/** @typescript */'),
        'transformable' => true,
        'inline' => false,
        'name' => null,
        'transformer' => null,
        'type' => null,
    ];

    yield '@typescript annotation with name' => [
        'reflection' => $reflection = FakeReflectionClass::create()->withDocComment('/** @typescript YoloClass */'),
        'transformable' => true,
        'inline' => false,
        'name' => 'YoloClass',
        'transformer' => null,
        'type' => null,
    ];

    yield '@typescript annotation with transformer' => [
        'reflection' => $reflection = FakeReflectionClass::create()->withDocComment('/** @typescript @typescript-transformer FakeTransformer */'),
        'transformable' => true,
        'inline' => false,
        'name' => null,
        'transformer' => 'FakeTransformer',
        'type' => null,
    ];

    yield '@typescript annotation with inline' => [
        'reflection' => $reflection = FakeReflectionClass::create()->withDocComment('/** @typescript @typescript-inline */'),
        'transformable' => true,
        'inline' => true,
        'name' => null,
        'transformer' => null,
        'type' => null,
    ];

    yield 'TypeScript attribute' => [
        'reflection' => new ReflectionClass(WithTypeScriptAttribute::class),
        'transformable' => true,
        'inline' => false,
        'name' => null,
        'transformer' => null,
        'type' => null,
    ];

    yield 'TypeScript attribute with name' => [
        'reflection' => new ReflectionClass(WithTypeScriptNamedAttribute::class),
        'transformable' => true,
        'inline' => false,
        'name' => 'YoloClass',
        'transformer' => null,
        'type' => null,
    ];

    yield 'TypeScript inline attribute' => [
        'reflection' => new ReflectionClass(WithTypeScriptInlineAttribute::class),
        'transformable' => true,
        'inline' => true,
        'name' => null,
        'transformer' => null,
        'type' => null,
    ];

    yield 'TypeScript transformer attribute' => [
        'reflection' => new ReflectionClass(WithTypeScriptTransformerAttribute::class),
        'transformable' => true,
        'inline' => false,
        'name' => null,
        'transformer' => DtoTransformer::class,
        'type' => null,
    ];

    yield 'TypeScript already transformed attribute' => [
        'reflection' => new ReflectionClass(WithAlreadyTransformedAttributeAttribute::class),
        'transformable' => true,
        'inline' => false,
        'name' => null,
        'transformer' => null,
        'type' => StructType::fromArray(['an_int' => 'int', 'a_bool' => 'bool']),
    ];
});
