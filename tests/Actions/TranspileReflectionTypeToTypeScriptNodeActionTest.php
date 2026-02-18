<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpTypeNodeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpMethodNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpPropertyNode;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Tests\Fakes\PropertyTypes\PhpTypesStub;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAny;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIntersection;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNull;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnknown;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptVoid;

it('can transpile php types', function (
    string $property,
    TypeScriptNode $expectedTypeScriptNode,
) {
    $transpiler = new TranspilePhpTypeNodeToTypeScriptNodeAction();

    $typeScriptNode = $transpiler->execute(
        (new PhpPropertyNode(new ReflectionProperty(PhpTypesStub::class, $property)))->getType(),
        new PhpClassNode(new ReflectionClass(PhpTypesStub::class))
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
            new TypeScriptReference(new ClassStringReference(Collection::class)),
            new TypeScriptReference(new ClassStringReference(Arrayable::class)),
        ]),
    ];

    yield [
        'bnf',
        new TypeScriptUnion([
            new TypeScriptIntersection([
                new TypeScriptReference(new ClassStringReference(Collection::class)),
                new TypeScriptReference(new ClassStringReference(Arrayable::class)),
            ]),
            new TypeScriptNull(),
        ]),
    ];

    yield [
        'self',
        new TypeScriptReference(new ClassStringReference(PhpTypesStub::class)),
    ];

    // @todo figure out this one
    //    yield [
    //        'static',
    //        new TypeReference(new ClassStringReference(PhpTypesStub::class)),
    //    ];

    // PHP 8.5+ resolves 'parent' to the actual class name instead of literal 'parent'
    // See: https://github.com/php/php-src/issues/18373
    yield [
        'parent',
        version_compare(PHP_VERSION, '8.5.0', '>=')
            ? new TypeScriptReference(new ClassStringReference(\stdClass::class))
            : new TypeScriptUnknown(),
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
        new TypeScriptReference(new ClassStringReference(Collection::class)),
    ];
});

it('can transpile a void return type', function () {
    $transpiler = new TranspilePhpTypeNodeToTypeScriptNodeAction();

    $typeScriptNode = $transpiler->execute(
        (new PhpMethodNode(new ReflectionMethod(PhpTypesStub::class, 'voidReturn')))->getReturnType(),
        new PhpClassNode(new ReflectionClass(PhpTypesStub::class))
    );

    expect($typeScriptNode)->toBeInstanceOf(TypeScriptVoid::class);
});
