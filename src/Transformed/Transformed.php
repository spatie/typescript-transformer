<?php

namespace Spatie\TypeScriptTransformer\Transformed;

use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptExport;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptForwardingNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use WeakMap;

class Transformed
{
    protected ?string $name;

    public bool $changed = true;

    /** @var WeakMap<Transformed, TypeReference[]> */
    public WeakMap $references;

    /** @var WeakMap<Transformed, null> */
    public WeakMap $referencedBy;

    /** @var array<string, TypeReference[]> */
    public array $missingReferences = [];

    /**
     * @param array<string> $location
     */
    public function __construct(
        public TypeScriptNode $typeScriptNode,
        public Reference $reference,
        public array $location,
        public bool $export = true,
    ) {
        $this->references = new WeakMap();
        $this->referencedBy = new WeakMap();
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
        $this->changed = false;

        if ($this->export === false) {
            return $this->typeScriptNode;
        }

        if (! $this->typeScriptNode instanceof TypeScriptNamedNode && ! $this->typeScriptNode instanceof TypeScriptForwardingNamedNode) {
            return $this->typeScriptNode;
        }

        return new TypeScriptExport($this->typeScriptNode);
    }

    public function addMissingReference(
        string|Reference $key,
        TypeReference $typeReference
    ): void {
        if ($key instanceof Reference) {
            $key = $key->getKey();
        }

        if(! array_key_exists($key, $this->missingReferences)) {
            $this->missingReferences[$key] = [];
        }

        $this->missingReferences[$key][] = $typeReference;
    }

    public function isMissingReference(string $key)
    {
        return array_key_exists($key, $this->missingReferences);
    }

    public function markMissingReferenceFound(
        Transformed $transformed
    ): void {
        $key = $transformed->reference->getKey();

        $typeReferences = $this->missingReferences[$key];

        foreach ($typeReferences as $typeReference) {
            $typeReference->connect($transformed);
        }

        $this->references[$transformed] = $typeReferences;

        unset($this->missingReferences[$key]);
    }

    public function markReferenceRemoved(
        Transformed $transformed
    ) {
        $typeReferences = $this->references[$transformed];

        foreach ($typeReferences as $typeReference) {
            $typeReference->unconnect();
        }

        unset($this->references[$transformed]);

        $this->missingReferences = $typeReferences;
    }

    public function markAsChanged(): void
    {
        $this->changed = true;
    }
}
