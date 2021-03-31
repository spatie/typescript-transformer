<?php

namespace Spatie\TypeScriptTransformer\Tests\TypeReflectors;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\TypeReflectors\MethodParameterTypeReflector;
use Spatie\TypeScriptTransformer\TypeReflectors\MethodReturnTypeReflector;

class MethodReturnTypeReflectorTest extends TestCase
{
    /** @test */
    public function it_can_reflect_from_reflection()
    {
        $class = new class {
            public function m1(): int
            {
                return 42;
            }

            public function m2(): ?int
            {
                return 42;
            }

            public function m3(): int|float
            {
                return 42;
            }

            public function m4(): int|float|null
            {
                return 42;
            }

            public function m5()
            {
                return 42;
            }
        };

        $this->assertEquals(
            'int',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm1'))->reflect()
        );

        $this->assertEquals(
            '?int',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm2'))->reflect()
        );

        $this->assertEquals(
            'int|float',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm3'))->reflect()
        );

        $this->assertEquals(
            'int|float|null',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm4'))->reflect()
        );

        $this->assertEquals(
            'any',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm5'))->reflect()
        );
    }

    /** @test */
    public function it_can_reflect_from_docblock()
    {
        $class = new class {
            /** @return int */
            public function m1()
            {
                return 42;
            }

            /** @return ?int */
            public function m2()
            {
                return 42;
            }

            /** @return int|float */
            public function m3()
            {
                return 42;
            }

            /** @return int|float|null */
            public function m4()
            {
                return 42;
            }

            public function m5()
            {
                return 42;
            }
        };

        $this->assertEquals(
            'int',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm1'))->reflect()
        );

        $this->assertEquals(
            '?int',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm2'))->reflect()
        );

        $this->assertEquals(
            'int|float',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm3'))->reflect()
        );

        $this->assertEquals(
            'int|float|null',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm4'))->reflect()
        );

        $this->assertEquals(
            'any',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm5'))->reflect()
        );
    }

    /** @test */
    public function it_can_reflect_from_attribute()
    {
        $class = new class {
            #[LiteralTypeScriptType('Integer')]
            public function m1()
            {
                return 42;
            }
        };

        $this->assertEquals(
            'Integer',
            (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm1'))->reflect()
        );
    }
}
