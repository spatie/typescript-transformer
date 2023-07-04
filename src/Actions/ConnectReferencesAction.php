<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ConnectReferencesAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
        public TypeScriptTransformerLog $log,
        protected VisitTypeScriptTreeAction $visitTypeScriptTreeAction = new VisitTypeScriptTreeAction(),
    ) {
    }

    /**
     * @param  array<Transformed>  $transformed
     */
    public function execute(array $transformed): ReferenceMap
    {
        $referenceMap = new ReferenceMap();

        foreach ($transformed as $transformedItem) {
            if ($transformedItem->reference) {
                $referenceMap->add($transformedItem);
            }
        }

        foreach ($transformed as $transformedItem) {
            $references = [];

            $this->visitTypeScriptTreeAction->execute(
                $transformedItem->typeScriptNode,
                function (TypeReference $typeReference) use ($referenceMap, &$references, $transformedItem) {
                    $reference = $typeReference->reference;

                    if (! $referenceMap->has($reference)) {
                        $this->log->warning("Tried replacing reference to `{$reference->humanFriendlyName()}` in `{$transformedItem->reference->humanFriendlyName()}` but it was not found in the transformed types");

                        return;
                    }

                    $references[] = $reference;
                    $typeReference->connect($referenceMap->get($reference));
                },
                [TypeReference::class]
            );

            $transformedItem->references = $references;
        }

        return $referenceMap;
    }
}
