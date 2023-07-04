<?php

namespace Spatie\TypeScriptTransformer\Transformed;

use ReflectionClass;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\Import;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

class Transformed
{
    /**
     * @param array<string> $location
     * @param array<Reference> $references
     */
    public function __construct(
        public TypeScriptNode $typeScriptNode,
        public ?Reference $reference, // Not always referenceable
        public ?string $name, // Not always needs a name
        public bool $export, // Not always exportable
        public array $location,
        public array $references = [],
    ) {
    }
}

// Niet per se tied aan een ReflectionClass
// Heeft niet per se een naam -> enkel indien exportable en dus referencable
// Location duid een structuur aan, maar is niet per se een namespace, kan evengoed een collectie aan files zijn
