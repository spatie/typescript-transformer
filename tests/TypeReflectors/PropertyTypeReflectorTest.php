<?php

namespace Spatie\TypeScriptTransformer\Tests\TypeReflectors;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\TypeReflectors\MethodReturnTypeReflector;
use Spatie\TypeScriptTransformer\TypeReflectors\PropertyTypeReflector;

class PropertyTypeReflectorTest extends TestCase
{
    /** @test */
    public function it_can_reflect_from_reflection()
    {
        $class = new class {
            public int $p1;
            public ?int $p2;
            public int|float $p3;
            public int|float|null $p4;
            public $p5;
        };

        $this->assertEquals(
            'int',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p1'))->reflect()
        );

        $this->assertEquals(
            '?int',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p2'))->reflect()
        );

        $this->assertEquals(
            'int|float',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p3'))->reflect()
        );

        $this->assertEquals(
            'int|float|null',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p4'))->reflect()
        );

        $this->assertEquals(
            'any',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p5'))->reflect()
        );
    }

    /** @test */
    public function it_can_reflect_from_docblock()
    {
        $class = new class {
            /** @var int */
            public $p1;

            /** @var ?int */
            public $p2;

            /** @var int|float */
            public $p3;

            /** @var int|float|null */
            public $p4;

            public $p5;
        };

        $this->assertEquals(
            'int',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p1'))->reflect()
        );

        $this->assertEquals(
            '?int',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p2'))->reflect()
        );

        $this->assertEquals(
            'int|float',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p3'))->reflect()
        );

        $this->assertEquals(
            'int|float|null',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p4'))->reflect()
        );

        $this->assertEquals(
            'any',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p5'))->reflect()
        );
    }

    /** @test */
    public function it_can_reflect_from_attribute()
    {
        $class = new class {
            #[LiteralTypeScriptType('Integer')]
            public $p1;
        };

        $this->assertEquals(
            'Integer',
            (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p1'))->reflect()
        );
    }
}
