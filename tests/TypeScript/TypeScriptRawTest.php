<?php

use Spatie\TypeScriptTransformer\Attributes\AdditionalImport;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('passes through raw TypeScript', function () {
    $node = new TypeScriptRaw('Record<string, never>');

    expect($node->write(new WritingContext([])))->toBe('Record<string, never>');
});

it('replaces import names with resolved reference names', function () {
    $import = new AdditionalImport('types/components.ts', 'SomeComponent');

    $node = new TypeScriptRaw('Record<string, SomeComponent>', [$import]);

    $context = new WritingContext([
        $import->getReferenceKeys()['SomeComponent'] => 'SomeComponent',
    ]);

    expect($context->resolveReference($import->getReferenceKeys()['SomeComponent']))->toBe('SomeComponent');
    expect($node->write($context))->toBe('Record<string, SomeComponent>');
});

it('replaces import names with aliased names', function () {
    $import = new AdditionalImport('types/components.ts', 'SomeComponent');

    $node = new TypeScriptRaw('Record<string, SomeComponent>', [$import]);

    $context = new WritingContext([
        $import->getReferenceKeys()['SomeComponent'] => 'SomeComponentImport',
    ]);

    expect($context->resolveReference($import->getReferenceKeys()['SomeComponent']))->toBe('SomeComponentImport');
    expect($node->write($context))->toBe('Record<string, SomeComponentImport>');
});

it('does not replace names within dotted identifiers', function () {
    $import = new AdditionalImport('types/components.ts', 'Foo');

    $node = new TypeScriptRaw('Foo.Bar | Foo | Baz.Foo', [$import]);

    $context = new WritingContext([
        $import->getReferenceKeys()['Foo'] => 'FooImport',
    ]);

    expect($node->write($context))->toBe('Foo.Bar | FooImport | Baz.Foo');
});

it('resolves references via placeholder syntax', function () {
    $transformed = transformSingle(StringBackedEnum::class, new EnumTransformer());

    $node = new TypeScriptRaw('Record<string, %Enum%>', references: [
        'Enum' => StringBackedEnum::class,
    ]);

    $reference = $node->references['Enum'];
    $reference->connect($transformed);

    $context = new WritingContext([
        $reference->reference->getKey() => 'StringBackedEnum',
    ]);

    expect($node->write($context))->toBe('Record<string, StringBackedEnum>');
});

it('resolves unconnected references to undefined', function () {
    $node = new TypeScriptRaw('%User% | null', references: [
        'User' => 'Fake',
    ]);

    $context = new WritingContext([]);

    expect($node->write($context))->toBe('undefined | null');
});

it('accepts Reference objects directly', function () {
    $reference = new CustomReference('group', 'name');

    $node = new TypeScriptRaw('%Custom% | null', references: [
        'Custom' => $reference,
    ]);

    $tsRef = $node->references['Custom'];
    $tsRef->connect(new Transformed(new TypeScriptString(), $reference, []));

    $context = new WritingContext([
        $reference->getKey() => 'ResolvedName',
    ]);

    expect($node->write($context))->toBe('ResolvedName | null');
});
