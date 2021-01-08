<?php

namespace Spatie\TypeScriptTransformer\Tests\Collectors;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypeScriptTransformer\Collectors\AttributeCollector;
use Spatie\TypeScriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypeScriptTransformer\Structures\CollectedOccurrence;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptTransformerAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
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
        $this->assertEquals(CollectedOccurrence::create(
            new MyclabsEnumTransformer(),
            $reflection->getShortName()
        ), $this->collector->getCollectedOccurrence($reflection));
    }

    /** @test */
    public function it_will_read_overwritten_transformers()
    {
        $reflection = new ReflectionClass(WithTypeScriptTransformerAttribute::class);

        $this->assertTrue($this->collector->shouldCollect($reflection));
        $this->assertEquals(CollectedOccurrence::create(
            new DtoTransformer($this->config),
            $reflection->getShortName()
        ), $this->collector->getCollectedOccurrence($reflection));
    }
}
