<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\Visitor\Visitor;

class ConnectReferencesAction
{
    public function __construct()
    {
    }

    public function execute(TransformedCollection $collection): ReferenceMap
    {
        $referenceMap = new ReferenceMap();

        foreach ($collection as $transformed) {
            $referenceMap->add($transformed);
        }

        $visitor = Visitor::create()->before(function (TypeReference $typeReference, array &$metadata) use ($referenceMap) {
            /** @var Transformed $transformed */
            $transformed = $metadata['transformed'];

            if (! $referenceMap->has($typeReference->reference)) {
                TypeScriptTransformerLog::resolve()->warning("Tried replacing reference to `{$typeReference->reference->humanFriendlyName()}` in `{$transformed->reference->humanFriendlyName()}` but it was not found in the transformed types");

                return;
            }

            $transformedReference = $referenceMap->get($typeReference->reference);

            $transformed->references[] = $transformedReference;

            $typeReference->connect($transformedReference);
        }, [TypeReference::class]);

        foreach ($collection as $transformed) {
            $metadata = [
                'transformed' => $transformed,
            ];

            $visitor->execute($transformed->typeScriptNode, $metadata);
        }

        return $referenceMap;
    }
}
