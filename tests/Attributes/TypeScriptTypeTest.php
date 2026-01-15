<?php

use function Spatie\Snapshots\assertMatchesSnapshot;

use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
use Spatie\TypeScriptTransformer\Data\WriteableFile;

it('can output a single type', closure: function () {
    class TestSingleTypeScriptTypeAttribute
    {
        #[TypeScriptType('array<int,'.WriteableFile::class.'>')]
        public array $property;
    }

    assertMatchesSnapshot(classesToTypeScript([
        WriteableFile::class,
        TestSingleTypeScriptTypeAttribute::class,
    ]));
});

it('can output an object type', function () {
    class TestObjectTypeScriptTypeAttribute
    {
        #[TypeScriptType([
            'name' => 'string',
            'file' => WriteableFile::class,
        ])]
        public array $property;
    }

    assertMatchesSnapshot(classesToTypeScript([
        WriteableFile::class,
        TestObjectTypeScriptTypeAttribute::class,
    ]));
});
