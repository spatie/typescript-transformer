<?php

use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\TypeReflectors\MethodReturnTypeReflector;

it('can reflect from reflection', function () {
    $class = new class {
        public function m1(): int
        {
            return 42;
        }

        public function m2(): ?int
        {
            return 42;
        }

        public function m3(): int | float
        {
            return 42;
        }

        public function m4(): int | float | null
        {
            return 42;
        }

        public function m5()
        {
            return 42;
        }
    };

    assertEquals(
        'int',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm1'))->reflect()
    );

    assertEquals(
        '?int',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm2'))->reflect()
    );

    assertEquals(
        'int|float',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm3'))->reflect()
    );

    assertEquals(
        'int|float|null',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm4'))->reflect()
    );

    assertEquals(
        'unknown',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm5'))->reflect()
    );
});

it('can reflect from docblock', function () {
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

        /** @return array<array-key, string> */
        public function m6()
        {
            return [];
        }
    };

    assertEquals(
        'int',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm1'))->reflect()
    );

    assertEquals(
        '?int',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm2'))->reflect()
    );

    assertEquals(
        'int|float',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm3'))->reflect()
    );

    assertEquals(
        'int|float|null',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm4'))->reflect()
    );

    assertEquals(
        'unknown',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm5'))->reflect()
    );

    assertEquals(
        'array<array-key,string>',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm6'))->reflect()
    );
});

it('can reflect from attribute', function () {
    $class = new class {
        #[LiteralTypeScriptType('Integer')]
        public function m1()
        {
            return 42;
        }
    };

    assertEquals(
        'Integer',
        (string) MethodReturnTypeReflector::create(new ReflectionMethod($class, 'm1'))->reflect()
    );
});
