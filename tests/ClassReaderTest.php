<?php

namespace Spatie\TypescriptTransformer\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Exceptions\InvalidTransformerGiven;
use Spatie\TypescriptTransformer\Support\ClassReader;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;

class ClassReaderTest extends TestCase
{
    private ClassReader $reader;

    public function setUp(): void
    {
        parent::setUp();

        $this->reader = new ClassReader();
    }

    /** @test */
    public function default_case(): void
    {
        /**
         * @typescript
         */
        $fake = new class {
        };

        ['name' => $name] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertStringContainsString('class@anonymous', $name);
    }

    /** @test */
    public function default_file_case(): void
    {
        /**
         * @typescript OtherEnum
         */
        $fake = new class {
        };

        ['name' => $name] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertEquals('OtherEnum', $name);
    }

    /** @test */
    public function it_will_resolve_the_transformer()
    {
        /**
         * @typescript-transformer \Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer
         */
        $fake = new class {
        };

        $this->assertEquals('\\' . MyclabsEnumTransformer::class, $this->reader->forClass(
            new ReflectionClass($fake)
        )['transformer']);
    }

    /** @test */
    public function it_will_will_throw_an_exception_with_non_existing_transformers()
    {
        $this->expectException(InvalidTransformerGiven::class);
        $this->expectDeprecationMessageMatches("/does not exist!/");

        /**
         * @typescript-transformer \Spatie\TypescriptTransformer\Transformers\IDoNotExist
         */
        $fake = new class {
        };

        $this->reader->forClass(new ReflectionClass($fake));
    }

    /** @test */
    public function it_will_will_throw_an_exception_with_class_that_does_not_implement_transformer()
    {
        $this->expectException(InvalidTransformerGiven::class);
        $this->expectDeprecationMessageMatches("/does not implement the Transformer interface!/");

        /**
         * @typescript-transformer \Spatie\TypescriptTransformer\Structures\Type
         */
        $fake = new class {
        };

        $this->reader->forClass(new ReflectionClass($fake));
    }
}
