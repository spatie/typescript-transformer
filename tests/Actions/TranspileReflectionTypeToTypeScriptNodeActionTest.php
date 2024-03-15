<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Spatie\TypeScriptTransformer\Actions\TranspileReflectionTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Tests\Stubs\PhpTypesStub;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAny;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIntersection;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNull;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnknown;

it('can transpile php types', function (
    string $property,
    TypeScriptNode $expectedTypeScriptNode,
) {
    $transpiler = new TranspileReflectionTypeToTypeScriptNodeAction();

    $typeScriptNode = $transpiler->execute(
        (new ReflectionProperty(PhpTypesStub::class, $property))->getType(),
        new ReflectionClass(PhpTypesStub::class)
    );

    expect($typeScriptNode)->toBeInstanceOf($expectedTypeScriptNode::class);
    expect($typeScriptNode)->toEqual($expectedTypeScriptNode);
})->with(function () {
    yield [
        'string',
        new TypeScriptString(),
    ];

    yield [
        'bool',
        new TypeScriptBoolean(),
    ];

    yield [
        'int',
        new TypeScriptNumber(),
    ];

    yield [
        'float',
        new TypeScriptNumber(),
    ];

    yield [
        'mixed',
        new TypeScriptAny(),
    ];

    yield [
        'false',
        new TypeScriptBoolean(),
    ];

    yield [
        'true',
        new TypeScriptBoolean(),
    ];

    yield [
        'null',
        new TypeScriptNull(),
    ];

    yield [
        'nullable',
        new TypeScriptUnion([
            new TypeScriptString(),
            new TypeScriptNull(),
        ]),
    ];

    yield [
        'union',
        new TypeScriptUnion([
            new TypeScriptString(),
            new TypeScriptNumber(),
        ]),
    ];

    yield [
        'intersection',
        new TypeScriptIntersection([
            new TypeReference(new ClassStringReference(Collection::class)),
            new TypeReference(new ClassStringReference(Arrayable::class)),
        ]),
    ];

    yield [
        'bnf',
        new TypeScriptUnion([
            new TypeScriptIntersection([
                new TypeReference(new ClassStringReference(Collection::class)),
                new TypeReference(new ClassStringReference(Arrayable::class)),
            ]),
            new TypeScriptNull(),
        ]),
    ];

    yield [
        'self',
        new TypeReference(new ClassStringReference(PhpTypesStub::class)),
    ];

    // @todo figure out this one
    //    yield [
    //        'static',
    //        new TypeReference(new ClassStringReference(PhpTypesStub::class)),
    //    ];

    yield [
        'parent',
        new TypeScriptUnknown(),
    ];

    yield [
        'object',
        new TypeScriptObject([]),
    ];

    yield [
        'array',
        new TypeScriptArray([]),
    ];

    yield [
        'reference',
        new TypeReference(new ClassStringReference(Collection::class)),
    ];
});
