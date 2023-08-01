<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Visitor\Visitor;

class ConnectReferencesAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
        public TypeScriptTransformerLog $log,
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

        $visitor = Visitor::create()->before(function (TypeReference $typeReference, array $metadata) use ($referenceMap, &$references) {
            $reference = $typeReference->reference;

            if (! $referenceMap->has($reference)) {
                /** @var Transformed $transformed */
                $transformed = $metadata['transformed'];

                $this->log->warning("Tried replacing reference to `{$reference->humanFriendlyName()}` in `{$transformed->reference->humanFriendlyName()}` but it was not found in the transformed types");

                return;
            }

            $references[] = $reference;
            $typeReference->connect($referenceMap->get($reference));
        }, [TypeReference::class]);

        foreach ($collection as $transformed) {
            $references = [];

            $metadata = [
                'transformed' => $transformed,
            ];

            $visitor->execute($transformed->typeScriptNode, $metadata);

            $transformed->references = $references;
        }

        return $referenceMap;
    }
}
