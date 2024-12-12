<?php

use Spatie\TypeScriptTransformer\TypeReflectors\PromotedPropertyTypeReflector;
use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

it('can reflect from reflection', function () {
    $class = new class(0, 0, 0, 0, 0) {
        public function __construct(
            public int $p1,
            public ?int $p2,
            public int | float $p3,
            public int | float | null $p4,
            public $p5,
        ) {}
    };

    assertEquals(
        'int',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p1'))->reflect(),
    );

    assertEquals(
        '?int',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p2'))->reflect(),
    );

    assertEquals(
        'int|float',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p3'))->reflect(),
    );

    assertEquals(
        'int|float|null',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p4'))->reflect(),
    );

    assertEquals(
        'any',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p5'))->reflect(),
    );
});

it('can reflect from docblock', function () {
    $class = new class(0, 0, 0, 0, 0, []) {
        /**
         * @param int $p1
         * @param ?int $p2
         * @param int|float $p3
         * @param int|float|null $p4
         * @param array<array-key, string> $p6
         */
        public function __construct(
            public int $p1,
            public ?int $p2,
            public int | float $p3,
            public int | float | null $p4,
            public $p5,
            public array $p6,
        ) {}
    };

    assertEquals(
        'int',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p1'))->reflect(),
    );

    assertEquals(
        '?int',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p2'))->reflect(),
    );

    assertEquals(
        'int|float',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p3'))->reflect(),
    );

    assertEquals(
        'int|float|null',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p4'))->reflect(),
    );

    assertEquals(
        'any',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p5'))->reflect(),
    );

    assertEquals(
        'array<array-key,string>',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p6'))->reflect(),
    );
});

it('can reflect from attribute', function () {
    $class = new class(0){
        public function __construct(
            #[LiteralTypeScriptType('Integer')]
            public $p1,
        ) {}
    };

    assertEquals(
        'Integer',
        (string) PromotedPropertyTypeReflector::create(new ReflectionProperty($class, 'p1'))->reflect(),
    );
});
