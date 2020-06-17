<?php

namespace Spatie\TypescriptTransformer\Steps;

use Spatie\TypescriptTransformer\Structures\Collection;
use Spatie\TypescriptTransformer\Structures\Type;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class PersistTypesCollectionStep
{
    private TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function execute(Collection $collection): void
    {
        $this->config->ensureConfigIsValid();

        $this->ensureOutputFileExists();

        $namespaces = [];

        $rootTypes = [];

        foreach ($collection->getTypes() as $type) {
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
                fn (Type $type) => $type->transformed,
                $types
            ));

            $output .= PHP_EOL;

            $output .= "}".PHP_EOL;
        }

        $output .= join(PHP_EOL, array_map(
            fn (Type $type) => $type->transformed,
            $rootTypes
        ));

        file_put_contents($this->config->getOutputFile(), $output);
    }

    private function ensureOutputFileExists(): void
    {
        if (! file_exists(pathinfo($this->config->getOutputFile(), PATHINFO_DIRNAME))) {
            mkdir(pathinfo($this->config->getOutputFile(), PATHINFO_DIRNAME), 0755, true);
        }
    }
}
