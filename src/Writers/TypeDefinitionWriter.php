<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TypeDefinitionWriter implements Writer
{
    public function __construct(protected TypeScriptTransformerConfig $config)
    {}

    public function format(TypesCollection $collection): void
    {
        (new ReplaceSymbolsInCollectionAction())->execute($collection);
        $output = $this->config->getOutput();

        [$namespaces, $rootTypes] = $this->groupByNamespace($collection);

        foreach ($namespaces as $namespace => $types) {
            asort($types);

            $typescript = "declare namespace {$namespace} {".PHP_EOL;

            $typescript .= join(PHP_EOL, array_map(
                fn (TransformedType $type) => "export {$type->toString()};",
                $types
            ));


            $typescript .= PHP_EOL."}".PHP_EOL;

            $output->append($typescript, str_replace('.', '\\', $namespace));
        }

        $typescript = join(PHP_EOL, array_map(
            fn (TransformedType $type) => "export {$type->toString()};",
            $rootTypes
        ));

        $output->append($typescript, str_replace('.', '\\', 'Root'));

        $output->writeOut('d.ts');
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
