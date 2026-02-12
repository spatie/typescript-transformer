<?php

use Spatie\TypeScriptTransformer\Attributes\AdditionalImport;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;

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
