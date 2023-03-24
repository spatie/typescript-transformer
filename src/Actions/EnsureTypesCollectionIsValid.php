<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Exceptions\SymbolAlreadyExists;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class EnsureTypesCollectionIsValid
{
    public function execute(TypesCollection $typesCollection): void
    {
        $structure = [];

        foreach ($typesCollection as $type) {
            if ($type->inline) {
                continue;
            }

            $this->ensureTypeCanBeAdded($type, $structure);
        }
    }

    protected function ensureTypeCanBeAdded(
        Transformed $type,
        array &$structure,
    ): void {
        $namespace = array_reduce($type->name->namespaceSegments, function (array $checkedSegments, string $segment) use (&$structure) {
            $segments = array_merge($checkedSegments, [$segment]);

            $namespace = join('.', $segments);

            if (array_key_exists($namespace, $structure)) {
                if ($structure[$namespace]['kind'] !== 'namespace') {
                    throw SymbolAlreadyExists::whenAddingNamespace(
                        $namespace,
                        $structure[$namespace]
                    );
                }
            }

            $structure[$namespace] = [
                'kind' => 'namespace',
                'value' => str_replace('.', '\\', $namespace),
            ];

            return $segments;
        }, []);

        $namespacedType = join('.', array_merge($namespace, [$type->name->name]));

        if (array_key_exists($namespacedType, $structure)) {
            throw SymbolAlreadyExists::whenAddingType(
                $type->name->getFqcn(),
                $structure[$namespacedType]
            );
        }

        $structure[$namespacedType] = [
            'kind' => 'type',
            'value' => $type->name->getFqcn(),
        ];
    }
}
