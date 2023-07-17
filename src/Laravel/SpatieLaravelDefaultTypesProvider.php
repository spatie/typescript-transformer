<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\TypeScriptTransformer\DefaultTypeProviders\DefaultTypesProvider;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptExport;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGenericTypeVariable;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;

class SpatieLaravelDefaultTypesProvider implements DefaultTypesProvider
{
    public function provide(): array
    {
        $types = [];

        if (class_exists(\Spatie\LaravelOptions\Options::class)) {
            $types[] = new Transformed(
                new TypeScriptExport(new TypeScriptAlias(
                    new TypeScriptGeneric(
                        new TypeScriptIdentifier('Options'),
                        [
                            new TypeScriptGenericTypeVariable(
                                new TypeScriptIdentifier('TValue'),
                                default: new TypeScriptIdentifier('string'),
                            ),
                            new TypeScriptGenericTypeVariable(
                                new TypeScriptIdentifier('TLabel'),
                                default: new TypeScriptIdentifier('string'),
                            ),
                        ]
                    ),
                    new TypeScriptGeneric(
                        new TypeScriptIdentifier('Array'),
                        [
                            new TypeScriptObject([
                                new TypeScriptProperty('value', new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TValue'))),
                                new TypeScriptProperty('label', new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TLabel'))),
                            ]),
                        ],
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
