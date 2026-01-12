<?php

namespace Spatie\TypeScriptTransformer\Tests\Factories;

use Illuminate\Support\Str;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

class TransformedFactory
{
    /**
     * @param array<Transformed> $references
     * @param array<Transformed> $referencedBy
     */
    public function __construct(
        public TypeScriptNode $typeScriptNode,
        public ?Reference $reference = null,
        public ?array $location = null,
        public ?bool $export = null,
        public ?array $references = null,
        public ?array $referencedBy = null,
    ) {
    }

    public static function alias(
        string $name,
        TypeScriptNode $typeScriptNode,
        ?Reference $reference = null,
        ?array $location = null,
        bool $export = true,
        ?array $references = null,
        ?array $referencedBy = null,
    ): TransformedFactory {
        $reference = $reference ?? new CustomReference(
            'factory_alias',
            ($location !== null ? implode('.', $location) : '').$name
        );

        return new self(
            typeScriptNode: new TypeScriptAlias(new TypeScriptIdentifier($name), $typeScriptNode),
            reference: $reference,
            location: $location,
            export: $export,
            references: $references,
            referencedBy: $referencedBy
        );
    }

    public function build(): Transformed
    {
        $reference = $this->reference ?? new CustomReference('factory', Str::random(6));
        $location = $this->location ?? [];
        $export = $this->export ?? true;

        $transformed = new Transformed(
            typeScriptNode: $this->typeScriptNode,
            reference: $reference,
            location: $location,
            export: $export,
        );

        foreach ($this->references ?? [] as $reference) {
            $transformed->references[$reference->reference->getKey()] = [];
        }

        foreach ($this->referencedBy ?? [] as $reference) {
            $transformed->referencedBy[] = $reference->reference->getKey();
        }

        return $transformed;
    }
}
