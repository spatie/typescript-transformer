<?php

namespace Spatie\TypeScriptTransformer\Tests\Transformers;

use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\BackedEnum;
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
            new ReflectionClass(Enum::class),
            'Enum',
        ));

        $this->assertNull($transformer->transform(
            new ReflectionClass(DateTime::class),
            'Enum',
        ));
    }

    /** @test */
    public function it_can_transform_an_enum_into_type()
    {
        $transformer = new EnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
        );

        $type = $transformer->transform(
            new ReflectionClass(Enum::class),
            'Enum'
        );

        $this->assertEquals("'JS' | 'PHP'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
        $this->assertEquals('type', $type->keyword);
    }

    /** @test */
    public function it_can_transform_an_enum_into_enum()
    {
        $transformer = new EnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
        );

        $type = $transformer->transform(
            new ReflectionClass(Enum::class),
            'Enum'
        );

        $this->assertEquals("'JS' = 'JS', 'PHP' = 'PHP'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
        $this->assertEquals('enum', $type->keyword);
    }

    /** @test */
    public function it_can_transform_an_enum_with_backed_values()
    {
        $transformer = new EnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
        );

        $type = $transformer->transform(
            new ReflectionClass(BackedEnum::class),
            'BackedEnum'
        );

        $this->assertEquals("'JS' = 'js', 'PHP' = 'php'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
        $this->assertEquals('enum', $type->keyword);
    }
}
