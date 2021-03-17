<?php

namespace Spatie\TypeScriptTransformer\Tests\Collectors;

use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Collectors\AttributeCollector;
use Spatie\TypeScriptTransformer\Structures\CollectedOccurrence;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithAlreadyTransformedAndNameAttributeAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithAlreadyTransformedAttributeAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptTransformerAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\Types\StructType;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class AttributeCollectorTest extends TestCase
{
    private AttributeCollector $collector;

    private TypeScriptTransformerConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = TypeScriptTransformerConfig::create()->transformers([
            MyclabsEnumTransformer::class,
        ]);

        $this->collector = new AttributeCollector($this->config);
    }

    /** @test */
    public function it_will_not_collect_non_annotated_classes()
    {
        $class = new class('a') extends Enum {
            const A = 'a';
        };

        $reflection = new ReflectionClass(
            $class
        );

        $this->assertFalse($this->collector->shouldCollect($reflection));
    }

    /** @test */
    public function it_will_collect_classes_with_attributes()
    {
        $reflection = new ReflectionClass(WithTypeScriptAttribute::class);

        $this->assertTrue($this->collector->shouldCollect($reflection));
        $this->assertEquals(
            "export type WithTypeScriptAttribute = 'a' | 'b';",
            $this->collector->getTransformedType($reflection)->transformed
        );
    }

    /** @test */
    public function it_will_read_overwritten_transformers()
    {
        $reflection = new ReflectionClass(WithTypeScriptTransformerAttribute::class);

        $this->assertTrue($this->collector->shouldCollect($reflection));
        $this->assertEquals(
            'export type WithTypeScriptTransformerAttribute = {an_int: number;};',
            $this->collector->getTransformedType($reflection)->transformed
        );
    }

    /** @test */
    public function it_will_collect_classes_with_already_transformed_attributes()
    {
        $reflection = new ReflectionClass(WithAlreadyTransformedAttributeAttribute::class);

        $this->assertTrue($this->collector->shouldCollect($reflection));
        $this->assertEquals(
            'export type WithAlreadyTransformedAttributeAttribute = {an_int:number;a_bool:boolean;};',
            $this->collector->getTransformedType($reflection)->transformed
        );
    }

    /** @test */
    public function it_will_collect_classes_with_already_transformed_attributes_and_take_a_name_into_account()
    {
        $reflection = new ReflectionClass(WithAlreadyTransformedAndNameAttributeAttribute::class);

        $this->assertTrue($this->collector->shouldCollect($reflection));
        $this->assertEquals(
            'export type YoloClass = {an_int:number;a_bool:boolean;};',
            $this->collector->getTransformedType($reflection)->transformed
        );
    }
}
