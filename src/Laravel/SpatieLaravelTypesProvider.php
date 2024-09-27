<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGenericTypeVariable;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class SpatieLaravelTypesProvider implements TypesProvider
{
    public function provide(TypeScriptTransformerConfig $config, TransformedCollection $types): void
    {
        if (class_exists(\Spatie\LaravelOptions\Options::class)) {
            $optionsType = new Transformed(
                new TypeScriptAlias(
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
                ),
                new ClassStringReference(\Spatie\LaravelOptions\Options::class),
                ['Spatie', 'LaravelOptions'],
                true,
            );

            $types->add($optionsType);
        }
    }
}
