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
        /** @var \ArrayIterator|TransformedType[] $iterator */
        $iterator = $collection->getIterator();

        $iterator->uasort(function (TransformedType $a, TransformedType $b) {
            return strcmp($a->name, $b->name);
        });

        $output = $this->config->getOutput();

        foreach ($iterator as $namespace => $type) {
            if ($type->isInline) {
                continue;
            }

            $imports = implode(PHP_EOL, array_map(
                fn(string $path, string $module) => 'import {'.$module."} from '".$path."';",
                array_keys($type->imports),
                $type->imports
            ));
            $output->append(
                sprintf('%s%sexport %s;%s', $imports, PHP_EOL, $type->toString(), PHP_EOL),
                $namespace
            );
        }

        $output->writeOut('ts');
    }

    public function replacesSymbolsWithFullyQualifiedIdentifiers(): bool
    {
        return false;
    }
}
