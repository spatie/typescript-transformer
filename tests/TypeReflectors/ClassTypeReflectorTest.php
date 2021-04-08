<?php

namespace Spatie\TypeScriptTransformer\Tests\TypeReflectors;

use Generator;
use phpDocumentor\Reflection\Type;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithAlreadyTransformedAttributeAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptInlineAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptNamedAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptTransformerAttribute;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionClass;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\TypeReflectors\ClassTypeReflector;
use Spatie\TypeScriptTransformer\Types\StructType;

class ClassTypeReflectorTest extends TestCase
{
    /**
     * @test
     * @dataProvider reflectionClassesDataProvider
     *
     * @param \Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionClass $reflection
     * @param bool $transformable
     * @param bool $inline
     * @param string|null $name
     * @param string|null $transformer
     * @param \phpDocumentor\Reflection\Type|null $type
     */
    public function it_can_correctly_reflect_classes(
        FakeReflectionClass | ReflectionClass $reflection,
        bool $transformable,
        bool $inline,
        ?string $name,
        ?string $transformer,
        ?Type $type
    ) {
        $reflected = ClassTypeReflector::create($reflection);

        $this->assertEquals($transformable, $reflected->isTransformable());
        $this->assertEquals($inline, $reflected->isInline());
        $this->assertEquals($name, $reflected->getName());
        $this->assertEquals($transformer, $reflected->getTransformerClass());
        $this->assertEquals($type, $reflected->getType());
    }

    public function reflectionClassesDataProvider(): Generator
    {
        yield 'non transformable' => [
            'reflection' => $reflection = FakeReflectionClass::create(),
            'transformable' => false,
            'inline' => false,
            'name' => $reflection->getName(),
            'transformer' => null,
            'type' => null,
        ];

        yield '@typescript annotation' => [
            'reflection' => $reflection = FakeReflectionClass::create()->withDocComment('/** @typescript */'),
            'transformable' => true,
            'inline' => false,
            'name' => $reflection->getName(),
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
            'name' => $reflection->getName(),
            'transformer' => 'FakeTransformer',
            'type' => null,
        ];

        yield '@typescript annotation with inline' => [
            'reflection' => $reflection = FakeReflectionClass::create()->withDocComment('/** @typescript @typescript-inline */'),
            'transformable' => true,
            'inline' => true,
            'name' => $reflection->getName(),
            'transformer' => null,
            'type' => null,
        ];

        yield 'TypeScript attribute' => [
            'reflection' => new ReflectionClass(WithTypeScriptAttribute::class),
            'transformable' => true,
            'inline' => false,
            'name' => 'WithTypeScriptAttribute',
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
            'name' => 'WithTypeScriptInlineAttribute',
            'transformer' => null,
            'type' => null,
        ];

        yield 'TypeScript transformer attribute' => [
            'reflection' => new ReflectionClass(WithTypeScriptTransformerAttribute::class),
            'transformable' => true,
            'inline' => false,
            'name' => 'WithTypeScriptTransformerAttribute',
            'transformer' => DtoTransformer::class,
            'type' => null,
        ];

        yield 'TypeScript already transformed attribute' => [
            'reflection' => new ReflectionClass(WithAlreadyTransformedAttributeAttribute::class),
            'transformable' => true,
            'inline' => false,
            'name' => 'WithAlreadyTransformedAttributeAttribute',
            'transformer' => null,
            'type' => StructType::fromArray(['an_int' => 'int', 'a_bool' => 'bool']),
        ];
    }
}
