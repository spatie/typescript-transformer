<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInCollectionAction;
use Spatie\TypeScriptTransformer\Compactors\Compactor;
use Spatie\TypeScriptTransformer\Compactors\ConfigCompactor;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TypeDefinitionWriter implements Writer
{
    protected Compactor $compactor;

    public function __construct(TypeScriptTransformerConfig $config) {
        $this->compactor = new ConfigCompactor($config);
    }

    public function format(TypesCollection $collection): string
    {
        (new ReplaceSymbolsInCollectionAction())->execute($collection);

        [$namespaces, $rootTypes] = $this->groupByNamespace($collection);

        $output = '';

        foreach ($namespaces as $namespace => $types) {
            asort($types);
            $namespace = $this->compactor->compact($namespace);

            $output .= "declare namespace {$namespace} {".PHP_EOL;

            $output .= join(PHP_EOL, array_map(
                fn (TransformedType $type) => "export {$type->toString()}",
                $types
            ));


            $output .= PHP_EOL."}".PHP_EOL;
        }

        $output .= join(PHP_EOL, array_map(
            fn (TransformedType $type) => "export {$type->toString()}",
            $rootTypes
        ));

        return $output;
    }

    public function replacesSymbolsWithFullyQualifiedIdentifiers(): bool
    {
        return true;
    }

    protected function groupByNamespace(TypesCollection $collection): array
    {
        $namespaces = [];
        $rootTypes = [];

        foreach ($collection as $type) {
            if ($type->isInline) {
                continue;
            }

            $namespace = str_replace('\\', '.', $type->reflection->getNamespaceName());

            if (empty($namespace)) {
                $rootTypes[] = $type;

                continue;
            }

            array_key_exists($namespace, $namespaces)
                ? $namespaces[$namespace][] = $type
                : $namespaces[$namespace] = [$type];
        }

        ksort($namespaces);

        return [$namespaces, $rootTypes];
    }
}
