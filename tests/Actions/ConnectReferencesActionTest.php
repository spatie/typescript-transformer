<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\Console\WrappedArrayConsole;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Tests\Fakes\Circular\CircularA;
use Spatie\TypeScriptTransformer\Tests\Fakes\Circular\CircularB;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum;
use Spatie\TypeScriptTransformer\Tests\Support\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;

it('can connect references', function () {
    $class = new class () {
        public StringBackedEnum $enum;
    };

    $collection = new TransformedCollection([
        $transformedEnum = transformSingle(StringBackedEnum::class, new EnumTransformer()),
        $transformedClass = transformSingle($class, new AllClassTransformer()),
    ]);

    $action = new ConnectReferencesAction(TypeScriptTransformerLog::createNullLog());

    $action->execute($collection);

    ray($transformedClass, $transformedEnum);

    expect($transformedEnum->references)->toHaveCount(0);
    expect($transformedEnum->referencedBy)->toHaveCount(1);
    expect($transformedEnum->referencedBy)->toContain($transformedClass->reference->getKey());

    expect($transformedClass->references)->toHaveCount(1);
    expect($transformedClass->references)->toHaveKey($transformedEnum->reference->getKey());
    expect($transformedClass->referencedBy)->toHaveCount(0);

    expect($transformedClass->typeScriptNode->type->properties[0]->type)
        ->toBeInstanceOf(TypeReference::class)
        ->referenced->toBe($transformedEnum);
});

it('can connect two objects referencing each other', function () {
    $collection = new TransformedCollection([
        $circularA = transformSingle(CircularA::class, new AllClassTransformer()),
        $circularB = transformSingle(CircularB::class, new AllClassTransformer()),
    ]);

    $action = new ConnectReferencesAction(TypeScriptTransformerLog::createNullLog());

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
        ->toBeInstanceOf(TypeReference::class)
        ->referenced->toBe($circularB);

    expect($circularB->typeScriptNode->type->properties[0]->type)
        ->toBeInstanceOf(TypeReference::class)
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
        new TypeScriptTransformerLog($console = new WrappedArrayConsole())
    );

    $action->execute($collection);

    expect($transformedClass->references)->toHaveCount(0);
    expect($transformedClass->referencedBy)->toHaveCount(0);

    expect($transformedClass->typeScriptNode->type->properties[0]->type)
        ->toBeInstanceOf(TypeReference::class)
        ->referenced->toBeNull();

    expect($console->messages)->not()->toBeEmpty();
});
