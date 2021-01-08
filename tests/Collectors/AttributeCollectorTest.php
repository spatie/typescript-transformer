<?php

namespace Spatie\TypeScriptTransformer\Tests\Collectors;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypeScriptTransformer\Collectors\AttributeCollector;
use Spatie\TypeScriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypeScriptTransformer\Structures\CollectedOccurrence;
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
    public function it_will_collect_annotated_classes()
    {
        #[TypeScript]
        $class = new class('a') extends Enum {
            const A = 'a';
        };

        $reflection = new ReflectionClass(
            $class
        );

        $this->assertTrue($this->collector->shouldCollect($reflection));
        $this->assertEquals(CollectedOccurrence::create(
            new MyclabsEnumTransformer(),
            $reflection->getShortName()
        ), $this->collector->getCollectedOccurrence($reflection));
    }

    /** @test */
    public function it_will_read_overwritten_transformers()
    {
        /**
         * @typescript
         * @typescript-transformer \Spatie\TypeScriptTransformer\Transformers\DtoTransformer
         */
        $class = new class('a') extends Enum {
            const A = 'a';
        };

        $reflection = new ReflectionClass(
            $class
        );

        $this->assertTrue($this->collector->shouldCollect($reflection));
        $this->assertEquals(CollectedOccurrence::create(
            new DtoTransformer($this->config),
            $reflection->getShortName()
        ), $this->collector->getCollectedOccurrence($reflection));
    }

    /** @test */
    public function it_will_throw_an_exception_if_a_transformer_is_not_found()
    {
        $this->expectException(TransformerNotFound::class);

        /** @typescript */
        $class = new class {
        };

        $reflection = new ReflectionClass(
            $class
        );


        $this->collector->getCollectedOccurrence($reflection);
    }
}
