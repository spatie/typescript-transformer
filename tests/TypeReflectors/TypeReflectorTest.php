<?php

namespace Spatie\TypeScriptTransformer\Tests\TypeReflectors;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Annotations\FakeAnnotationsClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionProperty;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionType;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionUnionType;
use Spatie\TypeScriptTransformer\TypeReflectors\PropertyTypeReflector;

class TypeReflectorTest extends TestCase
{
    /**
     * @test
     * @dataProvider docblockTypesProvider
     *
     * @param string $input
     * @param string $outputType
     */
    public function it_can_resolve_types_from_docblocks(string $input, string $outputType)
    {
        $reflector = new PropertyTypeReflector(
            FakeReflectionProperty::create()->withDocComment("@var {$input}")
        );

        $this->assertEquals($outputType, (string) $reflector->reflect());
    }

    public function docblockTypesProvider(): array
    {
        return [
            ['int', 'int'],
            ['bool', 'bool'],
            ['string', 'string'],
            ['float', 'float'],
            ['mixed', 'mixed'],
            ['array', 'array'],

            ['bool|int', 'bool|int'],
            ['?int', '?int'],
            ['int[]', 'int[]'],
        ];
    }

    /** @test */
    public function it_will_handle_no_docblock()
    {
        $reflector = new PropertyTypeReflector(
            FakeReflectionProperty::create()
        );

        $this->assertEquals('any', (string) $reflector->reflect());
    }

    /** @test */
    public function it_can_handle_another_non_var_docblock()
    {
        $reflector = new PropertyTypeReflector(
            FakeReflectionProperty::create()->withDocComment('@method bla')
        );

        $this->assertEquals('any', (string) $reflector->reflect());
    }

    /** @test */
    public function it_can_handle_an_incorrect_docblock()
    {
        $reflector = new PropertyTypeReflector(
            FakeReflectionProperty::create()->withDocComment('@var int  bool')
        );

        $this->assertEquals('int', (string) $reflector->reflect());
    }

    /**
     * @test
     * @dataProvider reflectionTypesProvider
     *
     * @param string $input
     * @param bool $isBuiltIn
     * @param string $outputType
     */
    public function it_can_resolve_reflection_types(string $input, bool $isBuiltIn, string $outputType)
    {
        $reflection = FakeReflectionProperty::create()->withType(
            FakeReflectionType::create()->withIsBuiltIn($isBuiltIn)->withType($input)
        );

        $reflector = new PropertyTypeReflector($reflection);

        $this->assertEquals($outputType, (string) $reflector->reflect());
    }

    public function reflectionTypesProvider(): array
    {
        return [
            ['int', true, 'int'],
            ['bool', true, 'bool'],
            ['mixed', true, 'mixed'],
            ['string', true, 'string'],
            ['float', true, 'float'],
            ['array', true, 'array'],

            [Enum::class, false, '\\' . Enum::class],
        ];
    }

    /**
     * @test
     * @dataProvider ignoredTypesProvider
     *
     * @param string $reflection
     * @param string $docbloc
     * @param string $outputType
     */
    public function it_will_ignore_a_reflected_type_if_it_is_already_in_the_docblock(
        string $reflection,
        string $docbloc,
        string $outputType
    ) {
        $reflection = FakeReflectionProperty::create()
            ->withType(FakeReflectionType::create()->withType($reflection))
            ->withDocComment($docbloc);

        $reflector = new PropertyTypeReflector($reflection);

        $this->assertEquals($outputType, (string) $reflector->reflect());
    }

    public function ignoredTypesProvider(): array
    {
        return [
            ['int', 'int', 'int'],
            ['int|array', 'array', 'int|array'],
            ['int[]', 'array', 'int[]'],
            ['?int[]', 'array', '?int[]'],
        ];
    }

    /** @test */
    public function it_can_only_use_reflection_property_for_typing()
    {
        $reflection = FakeReflectionProperty::create()->withType(
            FakeReflectionType::create()->withIsBuiltIn(true)->withType('string')
        );

        $reflector = new PropertyTypeReflector($reflection);

        $this->assertEquals('string', (string) $reflector->reflect());
    }

    /**
     * @test
     * @dataProvider nullifiedTypesProvider
     *
     * @param string $docbloc
     * @param string $outputType
     */
    public function it_can_nullify_types_based_upon_reflection(string $docbloc, string $outputType)
    {
        $reflection = FakeReflectionProperty::create()->withType(
            FakeReflectionType::create()->withType('int')->withAllowsNull()
        )->withDocComment("@var {$docbloc}");

        $reflector = new PropertyTypeReflector($reflection);

        $this->assertEquals($outputType, (string) $reflector->reflect());
    }

    public function nullifiedTypesProvider(): array
    {
        return [
            ['', '?int'],
            ['?int', '?int'],
            ['int', '?int'],
            ['array|int', 'array|int|null'],
            ['array|int|null', 'array|int|null'],
            ['mixed', 'mixed'],
        ];
    }

    /** @test */
    public function it_can_use_an_union_type_with_reflection()
    {
        $reflection = FakeReflectionProperty::create()->withType(
            FakeReflectionUnionType::create()->withType(
                FakeReflectionType::create()->withType('int')->withAllowsNull(),
                FakeReflectionType::create()->withType('float'),
            )
        );

        $reflector = new PropertyTypeReflector($reflection);

        $this->assertEquals('int|float|null', (string) $reflector->reflect());
    }

    /** @test */
    public function it_can_use_a_transformable_attribute_as_type()
    {
        $class = new class() {
            #[LiteralTypeScriptType('EnumType[]')]
            public $literal;
        };

        $reflection = new ReflectionProperty($class, 'literal');

        $reflector = new PropertyTypeReflector($reflection);

        $this->assertEquals('EnumType[]', (string) $reflector->reflect());
    }

    /** @test */
    public function it_can_reflect_docblocks_without_a_complete_fsqen()
    {
        $this->assertEquals(
            '\\' . Dto::class,
            (string) PropertyTypeReflector::create(new ReflectionProperty(FakeAnnotationsClass::class, 'property'))->reflect()
        );

        $this->assertEquals(
            '\\' . Dto::class,
            (string) PropertyTypeReflector::create(new ReflectionProperty(FakeAnnotationsClass::class, 'fsqnProperty'))->reflect()
        );

        $this->assertEquals(
            '\\' . Dto::class . '[]',
            (string) PropertyTypeReflector::create(new ReflectionProperty(FakeAnnotationsClass::class, 'arrayProperty'))->reflect()
        );
    }
}
