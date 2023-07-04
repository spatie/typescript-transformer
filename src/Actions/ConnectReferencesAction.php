<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
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

    public function execute(TransformedCollection $collection): ReferenceMap
    {
        $referenceMap = new ReferenceMap();

        foreach ($collection as $transformed) {
            if ($transformed->reference) {
                $referenceMap->add($transformed);
            }
        }

        foreach ($collection as $transformed) {
            $references = [];

            $this->visitTypeScriptTreeAction->execute(
                $transformed->typeScriptNode,
                function (TypeReference $typeReference) use ($referenceMap, &$references, $transformed) {
                    $reference = $typeReference->reference;

                    if (! $referenceMap->has($reference)) {
                        $this->log->warning("Tried replacing reference to `{$reference->humanFriendlyName()}` in `{$transformed->reference->humanFriendlyName()}` but it was not found in the transformed types");

                        return;
                    }

                    $references[] = $reference;
                    $typeReference->connect($referenceMap->get($reference));
                },
                [TypeReference::class]
            );

            $transformed->references = $references;
        }

        return $referenceMap;
    }
}
