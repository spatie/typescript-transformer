<?php

namespace Spatie\TypescriptTransformer\Tests;

use Illuminate\Support\Str;
use ReflectionClass;
use Spatie\TypescriptTransformer\ClassReader;

class ClassReaderTest extends TestCase
{
    private ClassReader $reader;

    public function setUp() : void
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

        ['file' => $file, 'type' => $type] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertEquals('types/generated.d.ts', $file);
        $this->assertTrue(Str::startsWith($type, 'ClassReaderTest')); // Anonymous class :)
    }

    /** @test */
    public function default_file_case(): void
    {
        /**
         * @typescript OtherEnum
         */
        $fake = new class {
        };

        ['file' => $file, 'type' => $type] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertEquals('types/generated.d.ts', $file);
        $this->assertEquals('OtherEnum', $type);
    }

    /** @test */
    public function default_type_case(): void
    {
        /**
         * @typescript types/yetAnotherType.d.ts
         */
        $fake = new class {
        };

        ['file' => $file, 'type' => $type] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertEquals('types/yetAnotherType.d.ts', $file);
        $this->assertTrue(Str::startsWith($type, 'ClassReaderTest')); // Anonymous class :)
    }

    /** @test */
    public function default_type_and_file_case(): void
    {
        /**
         * @typescript AnotherEnum types/yetAnotherType.d.ts
         */
        $fake = new class {
        };

        ['file' => $file, 'type' => $type] = $this->reader->forClass(
            new ReflectionClass($fake)
        );

        $this->assertEquals('types/yetAnotherType.d.ts', $file);
        $this->assertEquals('AnotherEnum', $type);
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

        /** @typescript   /types/yetAnotherType.d.ts    */
        $fake = new class {
        };

        $this->assertEquals('types/yetAnotherType.d.ts', $this->reader->forClass(
            new ReflectionClass($fake)
        )['file']);

        /** @typescript AnotherEnum   /types/yetAnotherType.d.ts   */
        $fake = new class {
        };

        $this->assertEquals('types/yetAnotherType.d.ts', $this->reader->forClass(
            new ReflectionClass($fake)
        )['file']);
    }
}
