<?php

namespace Spatie\TypeScriptTransformer\Collections;

use Exception;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class ReferenceMap
{
    protected array $references = [];

    public function add(
        Transformed $transformed
    ): void {
        if ($transformed->reference === null) {
            throw new Exception('Can only add transformed items with a reference');
        }

        $this->references[$transformed->reference->getKey()] = $transformed;
    }

    public function has(Reference $reference): bool
    {
        return array_key_exists($reference->getKey(), $this->references);
    }

    public function get(
        Reference $reference
    ): Transformed {
        return $this->references[$reference->getKey()];
    }
}
