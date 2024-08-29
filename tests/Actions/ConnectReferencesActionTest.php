<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
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

    $referenceMap = app(ConnectReferencesAction::class)->execute($collection)->all();

    expect($referenceMap)
        ->toHaveCount(2)
        ->toBe([
            $transformedEnum->reference->getKey() => $transformedEnum,
            $transformedClass->reference->getKey() => $transformedClass,
        ]);

    expect($transformedEnum->references)->toHaveCount(0);

    expect($transformedClass->references)
        ->toHaveCount(1)
        ->toBe([$transformedEnum]);

    expect($transformedClass->typeScriptNode->type->properties[0]->type)
        ->toBeInstanceOf(TypeReference::class)
        ->referenced->toBe($transformedEnum);
});

it('can connect two objects referencing each other', function () {
    $collection = new TransformedCollection([
        $circularA = transformSingle(CircularA::class, new AllClassTransformer()),
        $circularB = transformSingle(CircularB::class, new AllClassTransformer()),
    ]);

    $referenceMap = app(ConnectReferencesAction::class)->execute($collection)->all();

    expect($referenceMap)
        ->toHaveCount(2)
        ->toBe([
            $circularA->reference->getKey() => $circularA,
            $circularB->reference->getKey() => $circularB,
        ]);

    expect($circularA->references)
        ->toHaveCount(1)
        ->toBe([$circularB]);

    expect($circularB->references)
        ->toHaveCount(1)
        ->toBe([$circularA]);

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

    $referenceMap = app(ConnectReferencesAction::class)->execute($collection)->all();

    expect($referenceMap)
        ->toHaveCount(1)
        ->toBe([
            $transformedClass->reference->getKey() => $transformedClass,
        ]);

    expect($transformedClass->references)
        ->toHaveCount(0);

    expect($transformedClass->typeScriptNode->type->properties[0]->type)
        ->toBeInstanceOf(TypeReference::class)
        ->referenced->toBeNull();

    expect(TypeScriptTransformerLog::resolve()->warningMessages)->not()->toBeEmpty();
});
