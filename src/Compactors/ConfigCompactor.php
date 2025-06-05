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
                function(string $prefix): string {
                    $prefix = str_replace("\\", ".", $prefix);
                    if (!str_ends_with($prefix, ".")) {
                        $prefix .= ".";
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

    public function compact(
        string $typescriptIdentifier
    ): string {
        $matchingPrefix = '';
        $matchingSuffix = '';
        foreach ($this->getPrefixes() as $prefix) {
            if (str_starts_with($typescriptIdentifier, $prefix)) {
                $matchingPrefix = $prefix;
                break;
            }
        }
        foreach ($this->getSuffixes() as $suffix) {
            if (str_ends_with($typescriptIdentifier, $suffix)) {
                $matchingSuffix = $suffix;
                break;
            }
        }
        if ($matchingSuffix !== '') {
            $typescriptIdentifier = substr($typescriptIdentifier, 0, -strlen($matchingSuffix));
        }
        $substr = substr($typescriptIdentifier, strlen($matchingPrefix));
        return $substr;
    }

}