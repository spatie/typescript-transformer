<?php

use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Annotations\FakeAnnotationsClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionProperty;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionType;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionUnionType;
use Spatie\TypeScriptTransformer\TypeReflectors\PropertyTypeReflector;

it('can resolve types from docblocks', function (string $input, string $outputType) {
    $reflector = new PropertyTypeReflector(
        FakeReflectionProperty::create()->withDocComment("@var {$input}")
    );

    assertEquals($outputType, (string) $reflector->reflect());
})->with('docblock_types');

it('will handle no docblock', function () {
    $reflector = new PropertyTypeReflector(
        FakeReflectionProperty::create()
    );

    assertEquals('any', (string) $reflector->reflect());
});

it('can handle another non var docblock', function () {
    $reflector = new PropertyTypeReflector(
        FakeReflectionProperty::create()->withDocComment('@method bla')
    );

    assertEquals('any', (string) $reflector->reflect());
});

it('can handle an incorrect docblock', function () {
    $reflector = new PropertyTypeReflector(
        FakeReflectionProperty::create()->withDocComment('@var int  bool')
    );

    assertEquals('int', (string) $reflector->reflect());
});

it('can resolve reflection types', function (string $input, bool $isBuiltIn, string $outputType) {
    $reflection = FakeReflectionProperty::create()->withType(
        FakeReflectionType::create()->withIsBuiltIn($isBuiltIn)->withType($input)
    );

    $reflector = new PropertyTypeReflector($reflection);

    assertEquals($outputType, (string) $reflector->reflect());
})->with('reflection_types');

it('will ignore a reflected type if it is already in the docblock', function (string $reflection, string $docbloc, string $outputType) {
    $reflection = FakeReflectionProperty::create()
        ->withType(FakeReflectionType::create()->withType($reflection))
        ->withDocComment($docbloc);

    $reflector = new PropertyTypeReflector($reflection);

    assertEquals($outputType, (string) $reflector->reflect());
})->with('ignored_types');

it('can only use reflection property for typing', function () {
    $reflection = FakeReflectionProperty::create()->withType(
        FakeReflectionType::create()->withIsBuiltIn(true)->withType('string')
    );

    $reflector = new PropertyTypeReflector($reflection);

    assertEquals('string', (string) $reflector->reflect());
});

it('can nullify types based upon reflection', function (string $docbloc, string $outputType) {
    $reflection = FakeReflectionProperty::create()->withType(
        FakeReflectionType::create()->withType('int')->withAllowsNull()
    )->withDocComment("@var {$docbloc}");

    $reflector = new PropertyTypeReflector($reflection);

    assertEquals($outputType, (string) $reflector->reflect());
})->with('nullified_types');

it('can use an union type with reflection', function () {
    $reflection = FakeReflectionProperty::create()->withType(
        FakeReflectionUnionType::create()->withType(
            FakeReflectionType::create()->withType('int')->withAllowsNull(),
            FakeReflectionType::create()->withType('float'),
        )
    );

    $reflector = new PropertyTypeReflector($reflection);

    assertEquals('int|float|null', (string) $reflector->reflect());
});

it('can use a transformable attribute as type', function () {
    $class = new class() {
        #[LiteralTypeScriptType('EnumType[]')]
        public $literal;
    };

    $reflection = new ReflectionProperty($class, 'literal');

    $reflector = new PropertyTypeReflector($reflection);

    assertEquals('EnumType[]', (string) $reflector->reflect());
});

it('can reflect docblocks without a complete fsqen', function () {
    assertEquals(
        '\\' . Dto::class,
        (string) PropertyTypeReflector::create(new ReflectionProperty(FakeAnnotationsClass::class, 'property'))->reflect()
    );

    assertEquals(
        '\\' . Dto::class,
        (string) PropertyTypeReflector::create(new ReflectionProperty(FakeAnnotationsClass::class, 'fsqnProperty'))->reflect()
    );

    assertEquals(
        '\\' . Dto::class . '[]',
        (string) PropertyTypeReflector::create(new ReflectionProperty(FakeAnnotationsClass::class, 'arrayProperty'))->reflect()
    );
});
