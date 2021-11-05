<?php

namespace Spatie\TypeScriptTransformer\Tests\Transformers;

use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;

class EnumTransformerTest extends TestCase
{
    private EnumTransformer $transformer;

    public function setUp(): void
    {
        parent::setUp();

        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Native enums not supported before PHP 8.1');
        }

        $this->transformer = new EnumTransformer();
    }

    /** @test */
    public function it_will_only_convert_enums()
    {
        $this->assertNotNull($this->transformer->transform(
            new ReflectionClass(Enum::class),
            'Enum',
        ));

        $this->assertNull($this->transformer->transform(
            new ReflectionClass(DateTime::class),
            'Enum',
        ));
    }

    /** @test */
    public function it_can_transform_an_enum()
    {
        $type = $this->transformer->transform(
            new ReflectionClass(Enum::class),
            'Enum'
        );

        $this->assertEquals("'JS' | 'PHP'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
        $this->assertFalse($type->isInline);
    }
}
