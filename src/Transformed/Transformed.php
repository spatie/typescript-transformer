<?php

namespace Spatie\TypeScriptTransformer\Transformed;

use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptExport;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptForwardingNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

class Transformed
{
    protected ?string $name;

    /**
     * @param  array<string>  $location
     * @param  array<Transformed>  $references
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

        if ($this->typeScriptNode instanceof TypeScriptNamedNode) {
            return $this->name = $this->typeScriptNode->getName();
        }

        if ($this->typeScriptNode instanceof TypeScriptForwardingNamedNode) {
            $exportableNode = $this->typeScriptNode;

            while ($exportableNode instanceof TypeScriptForwardingNamedNode) {
                $exportableNode = $exportableNode->getForwardedNamedNode();
            }

            return $this->name = $exportableNode->getName();
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

        if (! $this->typeScriptNode instanceof TypeScriptNamedNode && ! $this->typeScriptNode instanceof TypeScriptForwardingNamedNode) {
            TypeScriptTransformerLog::resolve()->warning("Could not export `{$this->reference->humanFriendlyName()}` because it is not exportable");

            return $this->typeScriptNode;
        }

        return new TypeScriptExport($this->typeScriptNode);
    }
}
