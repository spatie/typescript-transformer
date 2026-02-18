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

    expect($transformedEnum->references)->toHaveCount(0);
    expect($transformedEnum->referencedBy)->toHaveCount(1);
    expect($transformedEnum->referencedBy)->toContain($transformedClass->reference->getKey());

    expect($transformedClass->references)->toHaveCount(1);
    expect($transformedClass->references)->toHaveKey($transformedEnum->reference->getKey());
    expect($transformedClass->referencedBy)->toHaveCount(0);

    expect($transformedClass->typeScriptNode->type->properties[0]->type)
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

    expect($circularA->references)->toHaveCount(1);
    expect($circularA->references)->toHaveKey($circularB->reference->getKey());
    expect($circularA->referencedBy)->toHaveCount(1);
    expect($circularA->referencedBy)->toContain($circularB->reference->getKey());

    expect($circularB->references)->toHaveCount(1);
    expect($circularB->references)->toHaveKey($circularA->reference->getKey());
    expect($circularB->referencedBy)->toHaveCount(1);
    expect($circularB->referencedBy)->toContain($circularA->reference->getKey());

    expect($circularA->typeScriptNode->type->properties[0]->type)
        ->toBeInstanceOf(TypeScriptReference::class)
        ->referenced->toBe($circularB);

    expect($circularB->typeScriptNode->type->properties[0]->type)
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

    expect($transformedClass->references)->toHaveCount(0);
    expect($transformedClass->referencedBy)->toHaveCount(0);

    expect($transformedClass->typeScriptNode->type->properties[0]->type)
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

    expect($transformedRaw->references)->toHaveKey($transformedEnum->reference->getKey());
    expect($transformedEnum->referencedBy)->toContain($transformedRaw->reference->getKey());
});
