<?php

namespace Spatie\TypeScriptTransformer\Tests\Transformers;

use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\IntBackedEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\UnitEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\StringBackedEnum;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class EnumTransformerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Native enums not supported before PHP 8.1');
        }
    }

    /** @test */
    public function it_will_only_convert_enums()
    {
        $transformer = new EnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
        );

        $this->assertNotNull($transformer->transform(
            new ReflectionClass(StringBackedEnum::class),
            'Enum',
        ));

        $this->assertNull($transformer->transform(
            new ReflectionClass(DateTime::class),
            'Enum',
        ));
    }

    /** @test */
    public function it_does_not_transform_a_unit_enum()
    {
        $transformer = new EnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
        );

        $type = $transformer->transform(
            new ReflectionClass(UnitEnum::class),
            'Enum'
        );

        $this->assertNull($type);
    }

    /** @test */
    public function it_can_transform_a_backed_enum_into_enum()
    {
        $transformer = new EnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
        );

        $type = $transformer->transform(
            new ReflectionClass(StringBackedEnum::class),
            'Enum'
        );

        $this->assertEquals("'JS' = 'js', 'PHP' = 'php'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
        $this->assertEquals('enum', $type->keyword);
    }

    /** @test */
    public function it_can_transform_a_backed_enum_into_a_union()
    {
        $transformer = new EnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
        );

        $type = $transformer->transform(
            new ReflectionClass(StringBackedEnum::class),
            'Enum'
        );

        $this->assertEquals("'js' | 'php'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
        $this->assertEquals('type', $type->keyword);
    }

    /** @test */
    public function it_can_transform_a_backed_enum_with_integers_into_an_enm()
    {
        $transformer = new EnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
        );

        $type = $transformer->transform(
            new ReflectionClass(IntBackedEnum::class),
            'Enum'
        );

        $this->assertEquals("'JS' = 1, 'PHP' = 2", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
        $this->assertEquals('enum', $type->keyword);
    }

    /** @test */
    public function it_can_transform_a_backed_enum_with_integers_into_a_union()
    {
        $transformer = new EnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
        );

        $type = $transformer->transform(
            new ReflectionClass(IntBackedEnum::class),
            'Enum'
        );

        $this->assertEquals("1 | 2", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
        $this->assertEquals('type', $type->keyword);
    }
}
