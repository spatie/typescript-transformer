<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Exceptions\InvalidTransformerGiven;
use Spatie\TypeScriptTransformer\Support\ClassReader;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;

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
         * @typescript-transformer \Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer
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
         * @typescript-transformer \Spatie\TypeScriptTransformer\Transformers\IDoNotExist
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
         * @typescript-transformer \Spatie\TypeScriptTransformer\Structures\TransformedType
         */
        $fake = new class {
        };

        $this->reader->forClass(new ReflectionClass($fake));
    }
}
