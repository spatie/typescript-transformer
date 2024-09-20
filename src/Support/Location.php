<?php

namespace Spatie\TypeScriptTransformer\Support;

use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class Location
{
    /**
     * @param  array<string>  $segments
     * @param  array<Transformed>  $transformed
     */
    public function __construct(
        public array $segments,
        public array $transformed,
    ) {
    }

    public function getTransformedByReference(Reference $reference): ?Transformed
    {
        foreach ($this->transformed as $transformed) {
            if ($transformed->reference->getKey() === $reference->getKey()) {
                return $transformed;
            }
        }

        return null;
    }

    public function hasChanges(): bool
    {
        foreach ($this->transformed as $transformed) {
            if ($transformed->changed) {
                return true;
            }
        }

        return false;
    }

    public function hasReference(Reference $reference): bool
    {
        return $this->getTransformedByReference($reference) !== null;
    }
}
