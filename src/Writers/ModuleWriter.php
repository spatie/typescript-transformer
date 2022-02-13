<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ModuleWriter implements Writer
{
    public function __construct(protected TypeScriptTransformerConfig $config)
    {}

    public function format(TypesCollection $collection): void
    {
        $output = '';

        /** @var \ArrayIterator $iterator */
        $iterator = $collection->getIterator();

        $iterator->uasort(function (TransformedType $a, TransformedType $b) {
            return strcmp($a->name, $b->name);
        });

        $output = $this->config->getOutput();

        foreach ($iterator as $namespace => $type) {
            if ($type->isInline) {
                continue;
            }

            $output .= "export {$type->toString()};".PHP_EOL;
        }

        return $output;
    }

    public function replacesSymbolsWithFullyQualifiedIdentifiers(): bool
    {
        return false;
    }
}
