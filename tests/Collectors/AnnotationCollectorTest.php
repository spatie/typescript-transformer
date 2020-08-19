<?php

namespace Spatie\TypescriptTransformer\Tests\Collectors;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypescriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypescriptTransformer\Support\CollectedOccurrence;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypescriptTransformer\Transformers\DtoTransformer;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class AnnotationCollectorTest extends TestCase
{
    private AnnotationCollector $collector;

    private TypeScriptTransformerConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = TypeScriptTransformerConfig::create()->transformers([
            MyclabsEnumTransformer::class,
        ]);

        $this->collector = new AnnotationCollector($this->config);
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
        /** @typescript */
        $class = new class('a') extends Enum {
            const A = 'a';
        };

        $reflection = new ReflectionClass(
            $class
        );

        $this->assertTrue($this->collector->shouldCollect($reflection));
        $this->assertEquals(CollectedOccurrence::create(
            new MyclabsEnumTransformer(),
            get_class($class)
        ), $this->collector->getCollectedOccurrence($reflection));
    }

    /** @test */
    public function it_will_read_overwritten_transformers()
    {
        /**
         * @typescript
         * @typescript-transformer \Spatie\TypescriptTransformer\Transformers\DtoTransformer
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
            get_class($class)
        ), $this->collector->getCollectedOccurrence($reflection));
    }

    /** @test */
    public function it_will_throw_an_exception_if_a_transformer_is_not_found()
    {
        $this->expectException(TransformerNotFound::class);

        /** @typescript */
        $class = new class {};

        $reflection = new ReflectionClass(
            $class
        );


        $this->collector->getCollectedOccurrence($reflection);
    }
}
