<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class TypeDefinitionWriter implements Writer
{
    public function format(TypesCollection $collection): string
    {
        (new ReplaceSymbolsInCollectionAction())->execute($collection);

        [$namespaces, $rootTypes] = $this->groupByNamespace($collection);

        $output = '';

        foreach ($namespaces as $namespace => $types) {
            asort($types);

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
