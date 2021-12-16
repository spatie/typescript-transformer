<?php

namespace Spatie\TypeScriptTransformer\Tests\Transformers;

use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\SpatieEnum;
use Spatie\TypeScriptTransformer\Transformers\SpatieEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class SpatieEnumTransformerTest extends TestCase
{
    /** @test */
    public function it_will_only_convert_enums()
    {
        $transformer = new SpatieEnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
        );

        $this->assertNotNull($transformer->transform(
            new ReflectionClass(SpatieEnum::class),
            'State',
        ));

        $this->assertNull($transformer->transform(
            new ReflectionClass(DateTime::class),
            'State',
        ));
    }

    /** @test */
    public function it_can_transform_an_enum_into_a_type()
    {
        $transformer = new SpatieEnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
        );

        $type = $transformer->transform(
            new ReflectionClass(SpatieEnum::class),
            'FakeEnum'
        );

        $this->assertEquals("'draft' | 'published' | 'archived'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
        $this->assertEquals('type', $type->keyword);
    }

    /** @test */
    public function it_can_transform_an_enum_into_an_enum()
    {
        $transformer = new SpatieEnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
        );

        $type = $transformer->transform(
            new ReflectionClass(SpatieEnum::class),
            'FakeEnum'
        );

        $this->assertEquals("'draft' = 'Draft', 'published' = 'Published', 'archived' = 'Archived'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
        $this->assertEquals('enum', $type->keyword);
    }
}
