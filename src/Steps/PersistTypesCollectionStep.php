<?php

namespace Spatie\TypescriptTransformer\Steps;

use Spatie\TypescriptTransformer\Structures\TransformedType;
use Spatie\TypescriptTransformer\Structures\TypesCollection;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class PersistTypesCollectionStep
{
    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function execute(TypesCollection $collection): void
    {
        $this->ensureOutputFileExists();

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

        $output = '';

        foreach ($namespaces as $namespace => $types) {
            $output .= "namespace {$namespace} {".PHP_EOL;

            $output .= join(PHP_EOL, array_map(
                fn (TransformedType $type) => $type->transformed,
                $types
            ));

            $output .= PHP_EOL;

            $output .= "}".PHP_EOL;
        }

        $output .= join(PHP_EOL, array_map(
            fn (TransformedType $type) => $type->transformed,
            $rootTypes
        ));

        file_put_contents($this->config->getOutputFile(), $output);
    }

    protected function ensureOutputFileExists(): void
    {
        if (! file_exists(pathinfo($this->config->getOutputFile(), PATHINFO_DIRNAME))) {
            mkdir(pathinfo($this->config->getOutputFile(), PATHINFO_DIRNAME), 0755, true);
        }
    }
}
