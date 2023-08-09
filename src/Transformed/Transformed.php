<?php

namespace Spatie\TypeScriptTransformer\Transformed;

use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptExport;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptExportableNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptForwardingExportableNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

class Transformed
{
    protected ?string $name;

    /**
     * @param array<string> $location
     * @param array<Transformed> $references
     */
    public function __construct(
        public TypeScriptNode $typeScriptNode,
        public Reference $reference,
        public array $location,
        public bool $export = true,
        public array $references = [],
    ) {
    }

    public function getName(): ?string
    {
        if (isset($this->name)) {
            return $this->name;
        }

        if ($this->typeScriptNode instanceof TypeScriptExportableNode) {
            return $this->name = $this->typeScriptNode->getExportedName();
        }

        if ($this->typeScriptNode instanceof TypeScriptForwardingExportableNode) {
            $exportableNode = $this->typeScriptNode;

            while ($exportableNode instanceof TypeScriptForwardingExportableNode) {
                $exportableNode = $exportableNode->getForwardedExportableNode();
            }

            return $this->name = $exportableNode->getExportedName();
        }

        return null;
    }

    public function nameAs(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function prepareForWrite(): TypeScriptNode
    {
        if ($this->export === false) {
            return $this->typeScriptNode;
        }

        if (! $this->typeScriptNode instanceof TypeScriptExportableNode && ! $this->typeScriptNode instanceof TypeScriptForwardingExportableNode) {
            TypeScriptTransformerLog::resolve()->warning("Could not export `{$this->reference->humanFriendlyName()}` because it is not exportable");

            return $this->typeScriptNode;
        }

        return new TypeScriptExport($this->typeScriptNode);
    }
}
