<?php

use Illuminate\Support\Collection;
use Spatie\TypeScriptTransformer\ClassPropertyProcessors\FixArrayLikeStructuresClassPropertyProcessor;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;

it('replaces array like structures', function (
    string $property,
    TypeScriptNode $expected,
) {
    $class = new class () {
        /** @var array<int, bool> */
        public array $int_key_array;

        /** @var array<string, bool> */
        public array $string_key_array;

        /** @var array<array-key, bool> */
        public array $array_key_array;

        /** @var array<string|int, bool> */
        public array $union_key_array;

        /** @var array<bool> */
        public array $correct_array;

        /** @var bool[] */
        public array $correct_array_alternative;

        /** @var array */
        public array $missing_types_array;

        public array $no_annotation_array;
    };

    $object = transformSingle($class)->typeScriptNode->type;

    [$propertyNode] = array_values(array_filter(
        $object->properties,
        fn (TypeScriptProperty $propertyNode) => $propertyNode->name instanceof TypeScriptIdentifier && $propertyNode->name->name === $property
    ));

    $propertyNode = (new FixArrayLikeStructuresClassPropertyProcessor())->execute(
        reflection: new ReflectionProperty($class, $property),
        annotation: null,
        property: $propertyNode
    );

    expect($propertyNode->type)->toEqual(
        $expected
    );
})->with(function () {
    yield 'int key array' => [
        'int_key_array',
        new TypeScriptArray([
            new TypeScriptBoolean(),
        ]),
    ];

    yield 'string key array' => [
        'string_key_array',
        new TypeScriptGeneric(
            new TypeScriptIdentifier('Record'),
            [
                new TypeScriptString(),
                new TypeScriptBoolean(),
            ],
        ),
    ];

    yield 'array key array' => [
        'array_key_array',
        new TypeScriptGeneric(
            new TypeScriptIdentifier('Record'),
            [
                new TypeScriptUnion([
                    new TypeScriptString(),
                    new TypeScriptNumber(),
                ]),
                new TypeScriptBoolean(),
            ],
        ),
    ];

    yield 'union key array' => [
        'union_key_array',
        new TypeScriptGeneric(
            new TypeScriptIdentifier('Record'),
            [
                new TypeScriptUnion([
                    new TypeScriptString(),
                    new TypeScriptNumber(),
                ]),
                new TypeScriptBoolean(),
            ],
        ),
    ];

    yield 'correct array' => [
        'correct_array',
        new TypeScriptArray([
            new TypeScriptBoolean(),
        ]),
    ];

    yield 'correct array alternative' => [
        'correct_array_alternative',
        new TypeScriptArray([
            new TypeScriptBoolean(),
        ]),
    ];

    yield 'missing types array' => [
        'missing_types_array',
        new TypeScriptArray([]),
    ];

    yield 'no annotation array' => [
        'no_annotation_array',
        new TypeScriptArray([]),
    ];
});

it('replaces array like classes', function (
    string $property,
    TypeScriptNode $expected,
) {
    $class = new class () {
        /** @var Collection<int, bool> */
        public Collection $int_key_collection;

        /** @var Collection<string, bool> */
        public Collection $string_key_collection;

        /** @var Collection<array-key, bool> */
        public Collection $array_key_collection;

        /** @var Collection<string|int, bool> */
        public Collection $union_key_collection;

        /** @var Collection<bool> */
        public Collection $missing_key_collection;

        /** @var Collection */
        public Collection $missing_types_collection;

        /** @var Collection<string, int, bool> */
        public Collection $too_much_types_collection;

        public Collection $no_annotation_collection;
    };

    $object = transformSingle($class)->typeScriptNode->type;

    [$propertyNode] = array_values(array_filter(
        $object->properties,
        fn (TypeScriptProperty $propertyNode) => $propertyNode->name instanceof TypeScriptIdentifier && $propertyNode->name->name === $property
    ));

    $propertyNode = (new FixArrayLikeStructuresClassPropertyProcessor(
        arrayLikeClassesToReplace: [Collection::class],
    ))->execute(
        reflection: new ReflectionProperty($class, $property),
        annotation: null,
        property: $propertyNode
    );

    expect($propertyNode->type)->toEqual(
        $expected
    );
})->with(function () {
    yield 'int key collection' => [
        'int_key_collection',
        new TypeScriptArray([
            new TypeScriptBoolean(),
        ]),
    ];

    yield 'string key collection' => [
        'string_key_collection',
        new TypeScriptGeneric(
            new TypeScriptIdentifier('Record'),
            [
                new TypeScriptString(),
                new TypeScriptBoolean(),
            ],
        ),
    ];

    yield 'array key collection' => [
        'array_key_collection',
        new TypeScriptGeneric(
            new TypeScriptIdentifier('Record'),
            [
                new TypeScriptUnion([
                    new TypeScriptString(),
                    new TypeScriptNumber(),
                ]),
                new TypeScriptBoolean(),
            ],
        ),
    ];

    yield 'union key collection' => [
        'union_key_collection',
        new TypeScriptGeneric(
            new TypeScriptIdentifier('Record'),
            [
                new TypeScriptUnion([
                    new TypeScriptString(),
                    new TypeScriptNumber(),
                ]),
                new TypeScriptBoolean(),
            ],
        ),
    ];

    yield 'missing key collection' => [
        'missing_key_collection',
        new TypeScriptArray([
            new TypeScriptBoolean(),
        ]),
    ];

    yield 'missing types collection' => [
        'missing_types_collection',
        new TypeReference(new ClassStringReference(Collection::class)),
    ];

    yield 'too much types collection' => [
        'too_much_types_collection',
        new TypeScriptGeneric(
            new TypeReference(new ClassStringReference(Collection::class)),
            [
                new TypeScriptString(),
                new TypeScriptNumber(),
                new TypeScriptBoolean(),
            ],
        ),
    ];

    yield 'no annotation collection' => [
        'no_annotation_collection',
        new TypeReference(new ClassStringReference(Collection::class)),
    ];
});
