<?php

use function Spatie\Snapshots\assertMatchesSnapshot;

use Spatie\TypeScriptTransformer\Attributes\AdditionalImport;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

it('can output a single type', function () {
    class TestSingleLiteralTypeScriptTypeAttribute
    {
        #[LiteralTypeScriptType('Array<{label: string, value: string}>')]
        public array $property;
    }

    assertMatchesSnapshot(classesToTypeScript([TestSingleLiteralTypeScriptTypeAttribute::class]));
});

it('can output an object type', function () {
    class TestObjectLiteralTypeScriptTypeAttribute
    {
        #[LiteralTypeScriptType([
            'label' => 'string',
            'value' => 'string',
        ])]
        public array $property;
    }

    assertMatchesSnapshot(classesToTypeScript([TestObjectLiteralTypeScriptTypeAttribute::class]));
});

it('can output a type with additional imports', function () {
    class TestAdditionalImportLiteralTypeScriptTypeAttribute
    {
        #[LiteralTypeScriptType(
            'Record<string, SomeComponent>',
            additionalImports: [
                new AdditionalImport('types/components.ts', 'SomeComponent'),
            ]
        )]
        public array $property;
    }

    $output = classesToTypeScript([TestAdditionalImportLiteralTypeScriptTypeAttribute::class]);

    expect($output)->toContain("import { SomeComponent } from './types/components'");
    expect($output)->toContain('Record<string, SomeComponent>');
});

it('can output a type with multiple additional imports', function () {
    class TestMultipleAdditionalImportsLiteralTypeScriptTypeAttribute
    {
        #[LiteralTypeScriptType(
            'Record<string, SomeComponent | OtherThing>',
            additionalImports: [
                new AdditionalImport('types/components.ts', ['SomeComponent', 'OtherThing']),
            ]
        )]
        public array $property;
    }

    $output = classesToTypeScript([TestMultipleAdditionalImportsLiteralTypeScriptTypeAttribute::class]);

    expect($output)->toContain('SomeComponent');
    expect($output)->toContain('OtherThing');
    expect($output)->toContain("from './types/components'");
});
