<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\Visitor\Visitor;

class ConnectReferencesAction
{
    public function __construct(
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

        $visitor = Visitor::create()->before(function (TypeReference $typeReference, array &$metadata) use ($referenceMap) {
            $reference = $typeReference->reference;

            if (! $referenceMap->has($reference)) {
                /** @var Transformed $transformed */
                $transformed = $metadata['transformed'];

                TypeScriptTransformerLog::resolve()->warning("Tried replacing reference to `{$reference->humanFriendlyName()}` in `{$transformed->reference->humanFriendlyName()}` but it was not found in the transformed types");

                return;
            }

            $transformed = $referenceMap->get($reference);

            $metadata['references'][] = $transformed;
            $typeReference->connect($transformed);
        }, [TypeReference::class]);

        foreach ($collection as $transformed) {
            $metadata = [
                'transformed' => $transformed,
                'references' => [],
            ];

            $visitor->execute($transformed->typeScriptNode, $metadata);

            $transformed->references = $metadata['references'];
        }

        return $referenceMap;
    }
}
