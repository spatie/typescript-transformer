<?php

namespace Spatie\TypeScriptTransformer\Tests\Collectors;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypeScriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypeScriptTransformer\Structures\CollectedOccurrence;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

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
        $this->assertStringContainsString(
            " = 'a' | 'yes' | 'no';",
            $this->collector->getTransformedType($reflection)->transformed,
        );
    }

    /** @test */
    public function it_will_collect_annotated_classes_and_use_the_given_name()
    {
        /** @typescript EnumTransformed */
        $class = new class('a') extends Enum {
            const A = 'a';
        };

        $reflection = new ReflectionClass(
            $class
        );

        $this->assertTrue($this->collector->shouldCollect($reflection));
        $this->assertEquals(
            "export type EnumTransformed = 'a' | 'yes' | 'no';",
            $this->collector->getTransformedType($reflection)->transformed,
        );
    }

    /** @test */
    public function it_will_read_overwritten_transformers()
    {
        /**
         * @typescript DtoTransformed
         * @typescript-transformer \Spatie\TypeScriptTransformer\Transformers\DtoTransformer
         */
        $class = new class('a') extends Enum {
            const A = 'a';

            public int $an_integer;
        };

        $reflection = new ReflectionClass(
            $class
        );

        $this->assertTrue($this->collector->shouldCollect($reflection));
        $this->assertStringContainsString(
            "export type DtoTransformed = {an_integer: number;}",
            $this->collector->getTransformedType($reflection)->transformed,
        );
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

        $this->collector->getTransformedType($reflection);
    }
}
