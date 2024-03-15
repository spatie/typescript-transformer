<?php

use Illuminate\Support\Collection;
use Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors\ReplaceLaravelCollectionByArrayClassPropertyProcessor;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;

it('replaces laravel collections', function (
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

    $propertyNode = (new ReplaceLaravelCollectionByArrayClassPropertyProcessor())->execute(
        reflection: new ReflectionProperty($class, $property),
        annotation: null,
        property: resolvePropertyNode($class, $property)
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
