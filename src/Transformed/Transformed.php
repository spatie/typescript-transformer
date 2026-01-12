<?php

namespace Spatie\TypeScriptTransformer\Transformed;

use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptExport;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptForwardingNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

class Transformed
{
    protected ?string $name;

    protected ?string $cached = null;

    public bool $changed = true;

    /** @var array<string, TypeReference[]> */
    public array $references = [];

    /** @var array<string> */
    public array $referencedBy = [];

    /** @var array<string, TypeReference[]> */
    public array $missingReferences = [];

    /**
     * @param array<string> $location
     */
    public function __construct(
        public TypeScriptNode $typeScriptNode,
        public Reference $reference,
        // TODO: location now depicts the path that the writer will output as namespace or file path
        // we probably want a more complex structure for the routes helper so that we also can write out helper files
        // - where should it be put and which writer to use
        public array $location,
        public bool $export = true,
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

    public function write(WritingContext $writingContext): string
    {
        if ($this->changed === false && $this->cached) {
            return $this->cached;
        }

        $this->changed = false;

        $node = $this->typeScriptNode;

        if ($this->export === true && ($node instanceof TypeScriptNamedNode || $node instanceof TypeScriptForwardingNamedNode)) {
            $node = new TypeScriptExport($node);
        }

        return $this->cached = $node->write($writingContext);
    }

    public function addMissingReference(
        string|Reference $key,
        TypeReference $typeReference
    ): void {
        if ($key instanceof Reference) {
            $key = $key->getKey();
        }

        if (! array_key_exists($key, $this->missingReferences)) {
            $this->missingReferences[$key] = [];
        }

        $this->missingReferences[$key][] = $typeReference;
    }

    public function markMissingReferenceFound(
        Transformed $transformed
    ): void {
        $key = $transformed->reference->getKey();

        $typeReferences = $this->missingReferences[$key];

        foreach ($typeReferences as $typeReference) {
            $typeReference->connect($transformed);
        }

        $this->references[$key] = $typeReferences;

        unset($this->missingReferences[$key]);

        $this->markAsChanged();

        $transformed->referencedBy[] = $this->reference->getKey();
    }

    public function markReferenceMissing(
        Transformed $transformed
    ): void {
        $key = $transformed->reference->getKey();

        $typeReferences = $this->references[$key];

        foreach ($typeReferences as $typeReference) {
            $typeReference->unconnect();
        }

        unset($this->references[$key]);

        $this->missingReferences[$key] = $typeReferences;

        $this->markAsChanged();
    }

    public function markAsChanged(): void
    {
        $this->changed = true;
    }

    public function equals(Transformed $other): bool
    {
        $equals = $this->getName() === $other->getName();

        if (! $equals) {
            return false;
        }

        if (count($this->referencedBy) !== count($other->referencedBy)
            || array_diff($this->referencedBy, $other->referencedBy) !== []
            || array_diff($other->referencedBy, $this->referencedBy) !== []) {
            return false;
        }

        if (! $this->compareTypeReferenceArrays($this->references, $other->references)) {
            return false;
        }

        return $this->compareTypeReferenceArrays($this->missingReferences, $other->missingReferences);
    }

    /**
     * @param array<string, TypeReference[]> $one
     * @param array<string, TypeReference[]> $two
     */
    private function compareTypeReferenceArrays(array $one, array $two): bool
    {
        if (array_keys($one) !== array_keys($two)) {
            return false;
        }

        foreach ($one as $key => $referencesOne) {
            $referencesTwo = $two[$key];

            if (count($referencesOne) !== count($referencesTwo)) {
                return false;
            }

            $referenceKeysOne = array_map(
                fn (TypeReference $typeReference) => $typeReference->reference->getKey(),
                $referencesOne
            );

            $referenceKeysTwo = array_map(
                fn (TypeReference $typeReference) => $typeReference->reference->getKey(),
                $referencesTwo
            );

            sort($referenceKeysOne);
            sort($referenceKeysTwo);

            if ($referenceKeysOne !== $referenceKeysTwo) {
                return false;
            }
        }

        return true;
    }
}
