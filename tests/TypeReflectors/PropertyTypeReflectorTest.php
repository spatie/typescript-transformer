<?php

use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\TypeReflectors\PropertyTypeReflector;

it('can reflect from reflection', function () {
    $class = new class {
        public int $p1;
        public ?int $p2;
        public int | float $p3;
        public int | float | null $p4;
        public $p5;
    };

    assertEquals(
        'int',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p1'))->reflect()
    );

    assertEquals(
        '?int',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p2'))->reflect()
    );

    assertEquals(
        'int|float',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p3'))->reflect()
    );

    assertEquals(
        'int|float|null',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p4'))->reflect()
    );

    assertEquals(
        'unknown',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p5'))->reflect()
    );
});

it('can reflect from docblock', function () {
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

        /** @var array<array-key, string> */
        public $p6;
    };

    assertEquals(
        'int',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p1'))->reflect()
    );

    assertEquals(
        '?int',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p2'))->reflect()
    );

    assertEquals(
        'int|float',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p3'))->reflect()
    );

    assertEquals(
        'int|float|null',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p4'))->reflect()
    );

    assertEquals(
        'unknown',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p5'))->reflect()
    );

    assertEquals(
        'array<array-key,string>',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p6'))->reflect()
    );
});

it('can reflect from attribute', function () {
    $class = new class {
        #[LiteralTypeScriptType('Integer')]
        public $p1;
    };

    assertEquals(
        'Integer',
        (string) PropertyTypeReflector::create(new ReflectionProperty($class, 'p1'))->reflect()
    );
});
