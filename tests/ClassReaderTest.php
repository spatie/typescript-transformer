<?php

namespace Spatie\TypeScriptTransformer\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\ClassReader;
use Spatie\TypeScriptTransformer\Exceptions\InvalidTransformerGiven;
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
    public function non_transformable_case(): void
    {
        $fake = new class {
        };

        ['transformable' => $transformable] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertFalse($transformable);
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
    public function inline_case(): void
    {
        /**
         * @typescript
         * @typescript-inline
         */
        $fake = new class {
        };

        ['inline' => $inline] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertTrue($inline);
    }
}
