<?php

namespace Spatie\TypeScriptTransformer\Tests\Factories;

use Illuminate\Support\Str;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

class TransformedFactory
{
    public function __construct(
        public TypeScriptNode $typeScriptNode,
        public ?Reference $reference = null,
        public ?array $location = null,
        public ?bool $export = null,
        public ?array $references = null,
    ) {
    }

    public static function alias(
        string $name,
        TypeScriptNode $typeScriptNode,
        ?Reference $reference = null,
        ?array $location = null,
        bool $export = true,
        ?array $references = null,
    ): TransformedFactory {
        $reference = $reference ?? new CustomReference(
            'factory_alias',
            ($location !== null ? implode('.', $location) : '').Str::slug($name)
        );

        return new self(
            typeScriptNode: new TypeScriptAlias(new TypeScriptIdentifier($name), $typeScriptNode),
            reference: $reference,
            location: $location,
            export: $export,
            references: $references
        );
    }

    public function build(): Transformed
    {
        $reference = $this->reference ?? new CustomReference('factory', Str::random(6));
        $location = $this->location ?? [];
        $export = $this->export ?? true;
        $references = $this->references ?? [];

        return new Transformed(
            typeScriptNode: $this->typeScriptNode,
            reference: $reference,
            location: $location,
            export: $export,
            references: $references,
        );
    }
}
