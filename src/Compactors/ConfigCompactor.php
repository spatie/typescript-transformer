<?php

namespace Spatie\TypeScriptTransformer\Compactors;

use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ConfigCompactor implements Compactor
{

    protected ?array $prefixes = null;

    protected ?array $suffixes = null;

    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config) {
        $this->config = $config;
    }

    /**
     * @return string[]
     */
    protected function getPrefixes(): array {
        if ($this->prefixes === null) {
            $this->prefixes = array_map(
                function (string $prefix): string {
                    if (str_ends_with($prefix, "\\")) {
                        $prefix = rtrim($prefix, "\\");
                    }
                    return $prefix;
                },
                $this->config->getCompactorPrefixes()
            );
        }
        return $this->prefixes;
    }

    /**
     * @return string[]
     */
    protected function getSuffixes(): array {
        if ($this->suffixes === null) {
            $this->suffixes = $this->config->getCompactorSuffixes();
        }
        return $this->suffixes;
    }

    public function removeSuffix(string $typeName): string {
        $matchingSuffix = '';
        foreach ($this->getSuffixes() as $suffix) {
            if (str_ends_with($typeName, $suffix)) {
                $matchingSuffix = $suffix;
                break;
            }
        }
        if ($matchingSuffix !== '') {
            $typeName = substr($typeName, 0, -strlen($matchingSuffix));
        }
        return $typeName;
    }

    public function removePrefix(string $namespace): string {
        $matchingPrefix = '';
        foreach ($this->getPrefixes() as $prefix) {
            if (str_starts_with($namespace, $prefix)) {
                $matchingPrefix = $prefix;
                break;
            }
        }
        $substr = substr($namespace, strlen($matchingPrefix));
        return ltrim($substr, '\\');
    }

}