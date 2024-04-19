<?php

use Pest\Expectation;
use Spatie\TypeScriptTransformer\References\ReflectionClassReference;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\HiddenAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\TypeScriptAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\TypeScriptLocationAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Support\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeProviders\TransformerTypesProvider;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

function getTestProvidedTypes(
    array $transformers = [new AllClassTransformer()],
): TransformedCollection {
    $provider = new TransformerTypesProvider(
        $transformers,
        [
            __DIR__.'/../Fakes/TypesToProvide',
        ]
    );

    $provider->provide(
        TypeScriptTransformerConfigFactory::create()->get(),
        $collection = new TransformedCollection()
    );

    return $collection;
}

it('will find types and takes attributes into account', function () {
    $collection = getTestProvidedTypes();

    expect($collection)->toHaveCount(3);
    expect(iterator_to_array($collection))->sequence(
        fn (Expectation $transformed) => $transformed
            ->toBeInstanceOf(Transformed::class)
            ->getName()->toBe('JustAnotherName')
            ->typeScriptNode->toEqual(new TypeScriptAlias(
                new TypeScriptIdentifier('JustAnotherName'),
                new TypeScriptObject([
                    new TypeScriptProperty('property', new TypeScriptString()),
                ])
            ))
            ->reference->toBeInstanceOf(ReflectionClassReference::class)
            ->reference->classString->toBe(TypeScriptAttributedClass::class)
            ->location->toBe(['Spatie', 'TypeScriptTransformer', 'Tests', 'Fakes', 'TypesToProvide']),
        fn (Expectation $transformed) => $transformed
            ->toBeInstanceOf(Transformed::class)
            ->getName()->toBe('TypeScriptLocationAttributedClass')
            ->typeScriptNode->toEqual(new TypeScriptAlias(
                new TypeScriptIdentifier('TypeScriptLocationAttributedClass'),
                new TypeScriptObject([
                    new TypeScriptProperty('property', new TypeScriptString()),
                ])
            ))
            ->reference->toBeInstanceOf(ReflectionClassReference::class)
            ->reference->classString->toBe(TypeScriptLocationAttributedClass::class)
            ->location->toBe(['App', 'Here']),
        fn (Expectation $transformed) => $transformed
            ->toBeInstanceOf(Transformed::class)
            ->getName()->toBe('SimpleClass')
            ->typeScriptNode->toEqual(new TypeScriptAlias(
                new TypeScriptIdentifier('SimpleClass'),
                new TypeScriptObject([
                    new TypeScriptProperty('stringProperty', new TypeScriptString()),
                    new TypeScriptProperty('constructorPromotedStringProperty', new TypeScriptString()),
                ])
            ))
            ->reference->toBeInstanceOf(ReflectionClassReference::class)
            ->reference->classString->toBe(SimpleClass::class)
            ->location->toBe(['Spatie', 'TypeScriptTransformer', 'Tests', 'Fakes', 'TypesToProvide']),
    );
});

it('will not find hidden classes', function () {
    $typeNames = array_map(
        fn (Transformed $transformed) => $transformed->reference->classString,
        iterator_to_array(getTestProvidedTypes())
    );

    expect($typeNames)
        ->not->toContain(HiddenAttributedClass::class)
        ->toContain(SimpleClass::class);
});

it('will only transform types it can transform', function () {
    $classTypes = array_map(
        fn (Transformed $transformed) => $transformed->reference->classString,
        iterator_to_array(getTestProvidedTypes([new AllClassTransformer()]))
    );

    expect($classTypes)
        ->not->toContain(StringBackedEnum::class)
        ->toContain(SimpleClass::class);

    $enumTypes = array_map(
        fn (Transformed $transformed) => $transformed->reference->classString,
        iterator_to_array(getTestProvidedTypes([new EnumTransformer()]))
    );

    expect($enumTypes)
        ->toContain(StringBackedEnum::class)
        ->not->toContain(SimpleClass::class);

    $allTypes = array_map(
        fn (Transformed $transformed) => $transformed->reference->classString,
        iterator_to_array(getTestProvidedTypes([new EnumTransformer(), new AllClassTransformer()]))
    );

    expect($allTypes)
        ->toContain(StringBackedEnum::class)
        ->toContain(SimpleClass::class);
});
