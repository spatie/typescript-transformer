<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Structures\NamespacedType;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ModuleWriter implements Writer
{
    public function format(TypesCollection $collection): string {
        $output = '';

        /** @var \ArrayIterator $iterator */
        $iterator = $collection->getIterator();

        $iterator->uasort(function (TransformedType $a, TransformedType $b) {
            return strcmp($a->name, $b->name);
        });

        $currentModuleNamespace = null;
        /** @var NamespacedType[] $typesByNamespace */
        $typesByNamespace = [];
        foreach ($iterator as $type) {
            /** @var TransformedType $type */
            if ($type->isInline) {
                continue;
            }
            if ($currentModuleNamespace === null) {
                $currentModuleNamespace = trim(NamespacedType::namespace($type->reflection->name), '\\');
            }

            $output .= "export {$type->toString()}" . PHP_EOL;

            if ($type->reflection->isUserDefined() && !$type->reflection->isInternal()) {
                foreach ($type->transformed->dependencies as $dependency) {
                    $namespacedType = new NamespacedType($dependency);
                    $typesByNamespace[$namespacedType->namespace][] = $namespacedType;
                }
            }
        }

        $import = '';
        foreach ($typesByNamespace as $namespace => $types) {
            $namespace = trim($namespace, '\\');
            if ($namespace === $currentModuleNamespace) {
                continue;
            }
            $import .= 'import {';
            $import .= join(
                ', ',
                array_map(
                    fn(NamespacedType $type) => $type->shortName,
                    $types
                )
            );
            $commonPrefix = NamespacedType::commonPrefix($namespace, $currentModuleNamespace);
            $thatRest = ltrim(substr($namespace, strlen($commonPrefix)), '\\');
            $currentRest = ltrim(substr($currentModuleNamespace, strlen($commonPrefix)), '\\');
            $sourceModulePath =
                join(
                    '/',
                    array_fill(0, substr_count($currentRest, '\\') + 1, '..')
                )
                . '/'
                . join(
                    '/',
                    explode('\\', $thatRest)
                );

            $import .= '} from "' . $sourceModulePath . "\";\n";
        }

        return $import . $output;
    }

    public function replacesSymbolsWithFullyQualifiedIdentifiers(): bool {
        return false;
    }
}
