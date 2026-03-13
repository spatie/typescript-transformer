<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Support\Loggers\ArrayLogger;
use Spatie\TypeScriptTransformer\Support\Loggers\NullLogger;
use Spatie\TypeScriptTransformer\Tests\Fakes\Circular\CircularA;
use Spatie\TypeScriptTransformer\Tests\Fakes\Circular\CircularB;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum;
use Spatie\TypeScriptTransformer\Tests\TestSupport\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;

it('can connect references', function () {
    $class = new class () {
        public StringBackedEnum $enum;
    };

    $collection = new TransformedCollection([
        $transformedEnum = transformSingle(StringBackedEnum::class, new EnumTransformer()),
        $transformedClass = transformSingle($class, new AllClassTransformer()),
    ]);

    $action = new ConnectReferencesAction(new NullLogger());

    $action->execute($collection);

    expect($transformedEnum->getReferences())->toHaveCount(0);
    expect($transformedEnum->getReferencedBy())->toHaveCount(1);
    expect($transformedEnum->getReferencedBy())->toContain($transformedClass->getReference()->getKey());

    expect($transformedClass->getReferences())->toHaveCount(1);
    expect($transformedClass->getReferences())->toHaveKey($transformedEnum->getReference()->getKey());
    expect($transformedClass->getReferencedBy())->toHaveCount(0);

    expect($transformedClass->getNode()->type->properties[0]->type)
        ->toBeInstanceOf(TypeScriptReference::class)
        ->referenced->toBe($transformedEnum);
});

it('can connect two objects referencing each other', function () {
    $collection = new TransformedCollection([
        $circularA = transformSingle(CircularA::class, new AllClassTransformer()),
        $circularB = transformSingle(CircularB::class, new AllClassTransformer()),
    ]);

    $action = new ConnectReferencesAction(new NullLogger());

    $action->execute($collection);

    expect($circularA->getReferences())->toHaveCount(1);
    expect($circularA->getReferences())->toHaveKey($circularB->getReference()->getKey());
    expect($circularA->getReferencedBy())->toHaveCount(1);
    expect($circularA->getReferencedBy())->toContain($circularB->getReference()->getKey());

    expect($circularB->getReferences())->toHaveCount(1);
    expect($circularB->getReferences())->toHaveKey($circularA->getReference()->getKey());
    expect($circularB->getReferencedBy())->toHaveCount(1);
    expect($circularB->getReferencedBy())->toContain($circularA->getReference()->getKey());

    expect($circularA->getNode()->type->properties[0]->type)
        ->toBeInstanceOf(TypeScriptReference::class)
        ->referenced->toBe($circularB);

    expect($circularB->getNode()->type->properties[0]->type)
        ->toBeInstanceOf(TypeScriptReference::class)
        ->referenced->toBe($circularA);
});

it('will write to the log when a reference cannot be found', function () {
    $class = new class () {
        public StringBackedEnum $enum;
    };

    $collection = new TransformedCollection([
        $transformedClass = transformSingle($class, new AllClassTransformer()),
    ]);


    $action = new ConnectReferencesAction(
        $console = new ArrayLogger()
    );

    $action->execute($collection);

    expect($transformedClass->getReferences())->toHaveCount(0);
    expect($transformedClass->getReferencedBy())->toHaveCount(0);

    expect($transformedClass->getNode()->type->properties[0]->type)
        ->toBeInstanceOf(TypeScriptReference::class)
        ->referenced->toBeNull();

    expect($console->messages)->not()->toBeEmpty();
});

it('can connect references within a TypeScriptRaw node', function () {
    $transformedEnum = transformSingle(StringBackedEnum::class, new EnumTransformer());

    $rawNode = new TypeScriptRaw('Record<string, %Enum%>', references: [
        'Enum' => StringBackedEnum::class,
    ]);

    $transformedRaw = new Transformed(
        $rawNode,
        new ClassStringReference('SomeClass'),
        [],
    );

    $collection = new TransformedCollection([
        $transformedEnum,
        $transformedRaw,
    ]);

    $action = new ConnectReferencesAction(new NullLogger());

    $action->execute($collection);

    expect($rawNode->references['Enum'])
        ->toBeInstanceOf(TypeScriptReference::class)
        ->referenced->toBe($transformedEnum);

    expect($transformedRaw->getReferences())->toHaveKey($transformedEnum->getReference()->getKey());
    expect($transformedEnum->getReferencedBy())->toContain($transformedRaw->getReference()->getKey());
});
