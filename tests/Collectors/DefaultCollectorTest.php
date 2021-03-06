<?php

namespace Spatie\TypeScriptTransformer\Tests\Collectors;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Exceptions\InvalidTransformerGiven;
use Spatie\TypeScriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithAlreadyTransformedAttributeAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptInlineAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptTransformerAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class DefaultCollectorTest extends TestCase
{
    private DefaultCollector $collector;

    private TypeScriptTransformerConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = TypeScriptTransformerConfig::create()->transformers([
            MyclabsEnumTransformer::class,
        ]);

        $this->collector = new DefaultCollector($this->config);
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

        $this->assertNull($this->collector->getTransformedType($reflection));
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

        $transformedType = $this->collector->getTransformedType($reflection);

        $this->assertNotNull($transformedType);
        $this->assertEquals(
            "'a' | 'yes' | 'no'",
            $transformedType->transformed,
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

        $transformedType = $this->collector->getTransformedType($reflection);

        $this->assertNotNull($transformedType);
        $this->assertEquals('EnumTransformed', $transformedType->name);
        $this->assertEquals(
            "'a' | 'yes' | 'no'",
            $transformedType->transformed,
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

        $transformedType = $this->collector->getTransformedType($reflection);

        $this->assertNotNull($transformedType);
        $this->assertEquals('DtoTransformed', $transformedType->name);
        $this->assertEquals(
            '{'.PHP_EOL.'an_integer: number;'.PHP_EOL.'}',
            $transformedType->transformed,
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

    /** @test */
    public function it_will_collect_classes_with_attributes()
    {
        $reflection = new ReflectionClass(WithTypeScriptAttribute::class);

        $transformedType = $this->collector->getTransformedType($reflection);

        $this->assertNotNull($transformedType);
        $this->assertEquals('WithTypeScriptAttribute', $transformedType->name);
        $this->assertEquals(
            "'a' | 'b'",
            $transformedType->transformed,
        );
    }

    /** @test */
    public function it_will_collect_attribute_overwritten_transformers()
    {
        $reflection = new ReflectionClass(WithTypeScriptTransformerAttribute::class);

        $transformedType = $this->collector->getTransformedType($reflection);

        $this->assertNotNull($transformedType);
        $this->assertEquals('WithTypeScriptTransformerAttribute', $transformedType->name);
        $this->assertEquals(
            '{'.PHP_EOL.'an_int: number;'.PHP_EOL.'}',
            $transformedType->transformed,
        );
    }

    /** @test */
    public function it_will_collect_classes_with_already_transformed_attributes()
    {
        $reflection = new ReflectionClass(WithAlreadyTransformedAttributeAttribute::class);

        $transformedType = $this->collector->getTransformedType($reflection);

        $this->assertNotNull($transformedType);
        $this->assertEquals(
            '{an_int:number;a_bool:boolean;}',
            $transformedType->transformed,
        );
    }

    /** @test */
    public function it_can_inline_collected_classes_with_annotations()
    {
        $reflection = new ReflectionClass(WithTypeScriptInlineAttribute::class);

        $transformedType = $this->collector->getTransformedType($reflection);

        $this->assertNotNull($transformedType);
        $this->assertTrue($transformedType->isInline);
    }

    /** @test */
    public function it_can_inline_collected_classes_with_attributes()
    {
        /**
         * @typescript
         * @typescript-inline
         */
        $class = new class('a') extends Enum {
            const A = 'a';
        };

        $transformedType = $this->collector->getTransformedType(new ReflectionClass($class));

        $this->assertNotNull($transformedType);
        $this->assertTrue($transformedType->isInline);
    }

    /** @test */
    public function it_will_will_throw_an_exception_with_non_existing_transformers()
    {
        $this->expectException(InvalidTransformerGiven::class);
        $this->expectDeprecationMessageMatches("/does not exist!/");

        /**
         * @typescript DtoTransformed
         * @typescript-transformer FAKE
         */
        $class = new class('a') extends Enum {
            const A = 'a';

            public int $an_integer;
        };

        $this->collector->getTransformedType(new ReflectionClass($class));
    }

    /** @test */
    public function it_will_will_throw_an_exception_with_class_that_does_not_implement_transformer()
    {
        $this->expectException(InvalidTransformerGiven::class);
        $this->expectDeprecationMessageMatches("/does not implement the Transformer interface!/");

        /**
         * @typescript-transformer \Spatie\TypeScriptTransformer\Structures\TransformedType
         */
        $class = new class {
        };

        $this->collector->getTransformedType(new ReflectionClass($class));
    }
}
