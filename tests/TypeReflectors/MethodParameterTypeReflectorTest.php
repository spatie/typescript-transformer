<?php

use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\TypeReflectors\MethodParameterTypeReflector;

it('can reflect from reflection', function () {
    $class = new class {
        public function method(
            int $int,
            ?int $nullable_int,
            int | float $union,
            null | int | float $nullable_union,
            $without_type
        ) {
        }
    };

    $parameters = (new ReflectionMethod($class, 'method'))->getParameters();

    assertEquals(
        'int',
        (string) MethodParameterTypeReflector::create($parameters[0])->reflect()
    );

    assertEquals(
        '?int',
        (string) MethodParameterTypeReflector::create($parameters[1])->reflect()
    );

    assertEquals(
        'int|float',
        (string) MethodParameterTypeReflector::create($parameters[2])->reflect()
    );

    assertEquals(
        'int|float|null',
        (string) MethodParameterTypeReflector::create($parameters[3])->reflect()
    );

    assertEquals(
        'any',
        (string) MethodParameterTypeReflector::create($parameters[4])->reflect()
    );
});

it('can reflect from docblock', function () {
    $class = new class {
        /**
         * @param int $int
         * @param ?int $nullable_int
         * @param int|float $union
         * @param int|float|null $nullable_union
         * @param array<array-key, string> $array
         * @param $without_type
         */
        public function method(
            $int,
            $nullable_int,
            $union,
            $nullable_union,
            $array,
            $without_type
        ) {
        }
    };

    $parameters = (new ReflectionMethod($class, 'method'))->getParameters();

    assertEquals(
        'int',
        (string) MethodParameterTypeReflector::create($parameters[0])->reflect()
    );

    assertEquals(
        '?int',
        (string) MethodParameterTypeReflector::create($parameters[1])->reflect()
    );

    assertEquals(
        'int|float',
        (string) MethodParameterTypeReflector::create($parameters[2])->reflect()
    );

    assertEquals(
        'int|float|null',
        (string) MethodParameterTypeReflector::create($parameters[3])->reflect()
    );

    assertEquals(
        'array<array-key,string>',
        (string) MethodParameterTypeReflector::create($parameters[4])->reflect()
    );

    assertEquals(
        'any',
        (string) MethodParameterTypeReflector::create($parameters[5])->reflect()
    );
});

it('cannot reflect from attribute', function () {
    $class = new class {
        #[LiteralTypeScriptType('int')]
        public function method(
            $int,
        ) {
        }
    };

    $parameters = (new ReflectionMethod($class, 'method'))->getParameters();

    assertEquals(
        'any',
        (string) MethodParameterTypeReflector::create($parameters[0])->reflect()
    );
});
