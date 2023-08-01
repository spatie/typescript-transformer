<?php

namespace Spatie\TypeScriptTransformer\Transformed;

use ReflectionClass;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

class Transformed
{
    /**
     * @param  array<string>  $location
     * @param  array<Reference>  $references
     */
    public function __construct(
        public TypeScriptNode $typeScriptNode,
        public Reference $reference,
        public string $name,
        public array $location,
        public array $references = [],
    ) {
    }
}
