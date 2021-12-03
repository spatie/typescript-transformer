<?php

namespace Spatie\TypeScriptTransformer\Tests\Transformers;

use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\SpatieEnum;
use Spatie\TypeScriptTransformer\Transformers\SpatieEnumNativeTransformer;

class SpatieEnumNativeTransformerTest extends TestCase
{
    private SpatieEnumNativeTransformer $transformer;

    public function setUp(): void
    {
        parent::setUp();

        $this->transformer = new SpatieEnumNativeTransformer();
    }

    /** @test */
    public function it_will_only_convert_enums()
    {
        $this->assertNotNull($this->transformer->transform(
            new ReflectionClass(SpatieEnum::class),
            'State',
        ));

        $this->assertNull($this->transformer->transform(
            new ReflectionClass(DateTime::class),
            'State',
        ));
    }

    /** @test */
    public function it_can_transform_an_enum()
    {
        $type = $this->transformer->transform(
            new ReflectionClass(SpatieEnum::class),
            'FakeEnum'
        );

        $this->assertEquals("'draft' = 'Draft', 'published' = 'Published', 'archived' = 'Archived'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
    }
}
