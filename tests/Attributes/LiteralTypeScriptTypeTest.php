<?php

use function Spatie\Snapshots\assertMatchesSnapshot;

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
