<?php

use Pest\Expectation;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\PhpClassReference;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\HiddenAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\OptionalAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\ReadonlyClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\TypeScriptAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\TypeScriptLocationAttributedClass;
use Spatie\TypeScriptTransformer\Tests\TestSupport\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformerProvider;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

function getTestProvidedTypes(
    array $transformers = [new AllClassTransformer()],
): TransformedCollection {
    $provider = new TransformerProvider(
        $transformers,
        [
            __DIR__.'/../Fakes/TypesToProvide',
        ]
    );

    $transformed = $provider->provide();

    return new TransformedCollection($transformed);
}

it('will find types and takes attributes into account', function () {
    $collection = getTestProvidedTypes();

    expect($collection)->toHaveCount(9);

    $typesToCheck = array_filter(
        iterator_to_array($collection),
        fn (Transformed $transformed) => in_array($transformed->reference->classString, [
            TypeScriptAttributedClass::class,
            TypeScriptLocationAttributedClass::class,
            OptionalAttributedClass::class,
            ReadonlyClass::class,
            SimpleClass::class,
        ])
    );

    usort($typesToCheck, fn (Transformed $a, Transformed $b) => $a->reference->classString <=> $b->reference->classString);

    expect($typesToCheck)->sequence(
        fn (Expectation $transformed) => $transformed
            ->toBeInstanceOf(Transformed::class)
            ->getName()->toBe('OptionalAttributedClass')
            ->typeScriptNode->toEqual(new TypeScriptAlias(
                new TypeScriptIdentifier('OptionalAttributedClass'),
                new TypeScriptObject([
                    new TypeScriptProperty('property', new TypeScriptString(), isOptional: true),
                ])
            ))
            ->reference->toBeInstanceOf(PhpClassReference::class)
            ->reference->classString->toBe(OptionalAttributedClass::class)
            ->location->toBe(['Spatie', 'TypeScriptTransformer', 'Tests', 'Fakes', 'TypesToProvide']),
        fn (Expectation $transformed) => $transformed
            ->toBeInstanceOf(Transformed::class)
            ->getName()->toBe('ReadonlyClass')
            ->typeScriptNode->toEqual(new TypeScriptAlias(
                new TypeScriptIdentifier('ReadonlyClass'),
                new TypeScriptObject([
                    new TypeScriptProperty('property', new TypeScriptString(), isReadonly: true),
                ])
            ))
            ->reference->toBeInstanceOf(PhpClassReference::class)
            ->reference->classString->toBe(ReadonlyClass::class)
            ->location->toBe(['Spatie', 'TypeScriptTransformer', 'Tests', 'Fakes', 'TypesToProvide']),
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
            ->reference->toBeInstanceOf(PhpClassReference::class)
            ->reference->classString->toBe(SimpleClass::class)
            ->location->toBe(['Spatie', 'TypeScriptTransformer', 'Tests', 'Fakes', 'TypesToProvide']),
        fn (Expectation $transformed) => $transformed
            ->toBeInstanceOf(Transformed::class)
            ->getName()->toBe('JustAnotherName')
            ->typeScriptNode->toEqual(new TypeScriptAlias(
                new TypeScriptIdentifier('JustAnotherName'),
                new TypeScriptObject([
                    new TypeScriptProperty('property', new TypeScriptString()),
                ])
            ))
            ->reference->toBeInstanceOf(PhpClassReference::class)
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
            ->reference->toBeInstanceOf(PhpClassReference::class)
            ->reference->classString->toBe(TypeScriptLocationAttributedClass::class)
            ->location->toBe(['App', 'Here']),
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
