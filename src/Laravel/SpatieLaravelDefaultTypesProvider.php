<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\TypeScriptTransformer\DefaultTypeProviders\DefaultTypesProvider;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptExport;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;

class SpatieLaravelDefaultTypesProvider implements DefaultTypesProvider
{
    public function provide(): array
    {
        $types = [];

        if (class_exists(\Spatie\LaravelOptions\Options::class)) {
            $types[] = new Transformed(
                new TypeScriptExport(new TypeScriptAlias(
                        new TypeScriptIdentifier('Options'),
                        new TypeScriptArray(
                            new TypeScriptObject([
                                new TypeScriptProperty('label', new TypeScriptString()),
                                new TypeScriptProperty('value', new TypeScriptString()),
                            ]),
                        )
                    )
                ),
                new ClassStringReference(\Spatie\LaravelOptions\Options::class),
                'Options',
                true,
                ['Spatie', 'LaravelOptions'],
            );
        }

        return $types;
    }
}
