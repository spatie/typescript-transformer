<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Compactors\Compactor;
use Spatie\TypeScriptTransformer\Compactors\ConfigCompactor;
use Spatie\TypeScriptTransformer\Structures\NamespacedType;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ModuleWriter implements Writer
{

    protected Compactor $compactor;

    public function __construct(TypeScriptTransformerConfig $config) {
        $this->compactor = new ConfigCompactor($config);
    }

    public function format(TypesCollection $collection): string {
        $output = '';

        /** @var \ArrayIterator $iterator */
        $iterator = $collection->getIterator();

        $iterator->uasort(function (TransformedType $a, TransformedType $b) {
            return strcmp($a->name, $b->name);
        });

        $currentModuleTsNamespace = null;
        /** @var NamespacedType[] $typesByNamespace */
        $typesByNamespace = [];
        foreach ($iterator as $type) {
            /** @var TransformedType $type */
            if ($type->isInline) {
                continue;
            }

            if ($currentModuleTsNamespace === null) {
                $currentModuleTsNamespace =
                    $this->compactor->removePrefix(
                        trim(
                            NamespacedType::namespace($type->reflection->name),
                            '\\'
                        )
                    );
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
            $tsNamespace = $this->compactor->removePrefix(
                trim($namespace, '\\')
            );

            if ($tsNamespace === $currentModuleTsNamespace) {
                continue;
            }
            $import .= 'import {';
            $import .= join(
                ', ',
                array_unique(
                    array_map(
                        fn(NamespacedType $type) => $this->compactor->removeSuffix($type->shortName),
                        $types
                    )
                )
            );
            $commonPrefix = NamespacedType::commonPrefix($tsNamespace, $currentModuleTsNamespace);
            $importedRest = ltrim(substr($tsNamespace, strlen($commonPrefix)), '\\');
            $currentRest = ltrim(substr($currentModuleTsNamespace, strlen($commonPrefix)), '\\');
            $backParts = array_fill(0, substr_count($currentRest, '\\'), '..');
            $sourceModulePath =
                    (
                    count($backParts) === 0
                        ? '.'
                        : join('/', $backParts)
                    )
                    . '/'
                    . join(
                        '/',
                        explode('\\', $importedRest)
                    );

            $import .= '} from "' . $sourceModulePath . "\";\n";
        }

        return $import . $output;
    }

    public function replacesSymbolsWithFullyQualifiedIdentifiers(): bool {
        return false;
    }
}
