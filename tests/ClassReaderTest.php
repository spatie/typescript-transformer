<?php

namespace Spatie\TypescriptTransformer\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\ClassReader;
use Spatie\TypescriptTransformer\Exceptions\InvalidTransformerGiven;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;

class ClassReaderTest extends TestCase
{
    private ClassReader $reader;

    public function setUp(): void
    {
        parent::setUp();

        $this->reader = new ClassReader('types/generated.d.ts');
    }

    /** @test */
    public function default_case(): void
    {
        /**
         * @typescript
         */
        $fake = new class {
        };

        ['file' => $file, 'name' => $name] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertEquals('types/generated.d.ts', $file);
        $this->assertEquals('class@anonymous', substr($name, 0, 15));
    }

    /** @test */
    public function default_file_case(): void
    {
        /**
         * @typescript OtherEnum
         */
        $fake = new class {
        };

        ['file' => $file, 'name' => $name] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertEquals('types/generated.d.ts', $file);
        $this->assertEquals('OtherEnum', $name);
    }

    /** @test */
    public function default_type_case(): void
    {
        /**
         * @typescript types/yetAnotherType.d.ts
         */
        $fake = new class {
        };

        ['file' => $file, 'name' => $name] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertEquals('types/yetAnotherType.d.ts', $file);
        $this->assertEquals('class@anonymous', substr($name, 0, 15));
    }

    /** @test */
    public function default_type_and_file_case(): void
    {
        /**
         * @typescript AnotherEnum types/yetAnotherType.d.ts
         */
        $fake = new class {
        };

        ['file' => $file, 'name' => $name] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertEquals('types/yetAnotherType.d.ts', $file);
        $this->assertEquals('AnotherEnum', $name);
    }

    /** @test */
    public function it_normalizes_file_paths(): void
    {
        /** @typescript */
        $fake = new class {
        };

        $this->assertEquals('types/yetAnotherType.d.ts', (new ClassReader(' /types/yetAnotherType.d.ts '))->forClass(
            new ReflectionClass($fake)
        )['file']);

        /** @typescript   /types/yetAnotherType.d.ts */
        $fake = new class {
        };

        $this->assertEquals('types/yetAnotherType.d.ts', $this->reader->forClass(
            new ReflectionClass($fake)
        )['file']);

        /** @typescript AnotherEnum   /types/yetAnotherType.d.ts */
        $fake = new class {
        };

        $this->assertEquals('types/yetAnotherType.d.ts', $this->reader->forClass(
            new ReflectionClass($fake)
        )['file']);
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
         * @typescript-transformer \Spatie\TypescriptTransformer\Type
         */
        $fake = new class {
        };

        $this->reader->forClass(new ReflectionClass($fake));
    }
}
