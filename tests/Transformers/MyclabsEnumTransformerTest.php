<?php

namespace Spatie\TypeScriptTransformer\Tests\Transformers;

use MyCLabs\Enum\Enum;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class MyclabsEnumTransformerTest extends TestCase
{
    /** @test */
    public function it_will_check_if_an_enum_can_be_transformed()
    {
        $transformer = new MyclabsEnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
        );

        $enum = new class('view') extends Enum {
            private const VIEW = 'view';
            private const EDIT = 'edit';
        };

        $noEnum = new class {
        };

        $this->assertNotNull($transformer->transform(new ReflectionClass($enum), 'Enum'));
        $this->assertNull($transformer->transform(new ReflectionClass($noEnum), 'Enum'));
    }

    /** @test */
    public function it_can_transform_an_enum_into_a_type()
    {
        $transformer = new MyclabsEnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
        );

        $enum = new class('view') extends Enum {
            private const VIEW = 'view';
            private const EDIT = 'edit';
        };

        $type = $transformer->transform(new ReflectionClass($enum), 'Enum');

        $this->assertEquals("'view' | 'edit'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertEquals('type', $type->keyword);
    }

    /** @test */
    public function it_can_transform_an_enum_into_an_enum()
    {
        $transformer = new MyclabsEnumTransformer(
            TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
        );

        $enum = new class('view') extends Enum {
            private const VIEW = 'view';
            private const EDIT = 'edit';
        };

        $type = $transformer->transform(new ReflectionClass($enum), 'Enum');

        $this->assertEquals("'VIEW' = 'view', 'EDIT' = 'edit'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertEquals('enum', $type->keyword);
    }
}
