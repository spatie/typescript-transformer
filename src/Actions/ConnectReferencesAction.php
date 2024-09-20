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
    protected Visitor $visitor;

    public function __construct(
        protected TypeScriptTransformerLog $log,
    ) {
        $this->visitor = $this->resolveVisitor();
    }

    /**
     * @param TransformedCollection|array<Transformed> $collection
     */
    public function execute(TransformedCollection|array $collection): ReferenceMap
    {
        $referenceMap = new ReferenceMap();

        foreach ($collection as $transformed) {
            $referenceMap->add($transformed);
        }

        foreach ($collection as $transformed) {
            $metadata = [
                'transformed' => $transformed,
                'referenceMap' => $referenceMap,
            ];

            $this->visitor->execute($transformed->typeScriptNode, $metadata);
        }

        return $referenceMap;
    }

    protected function resolveVisitor(): Visitor
    {
        return Visitor::create()->before(function (TypeReference $typeReference, array &$metadata) {
            /** @var Transformed $transformed */
            $transformed = $metadata['transformed'];

            /** @var ReferenceMap $referenceMap */
            $referenceMap = $metadata['referenceMap'];

            if (! $referenceMap->has($typeReference->reference)) {
                $transformed->addMissingReference($typeReference->reference, $typeReference);

                $this->log->warning("Tried replacing reference to `{$typeReference->reference->humanFriendlyName()}` in `{$transformed->reference->humanFriendlyName()}` but it was not found in the transformed types");

                return;
            }

            $transformedReference = $referenceMap->get($typeReference->reference);

            if(! $transformed->references->offsetExists($transformedReference)) {
                $transformed->references[$transformedReference] = [];
            }

            $transformed->references[$transformedReference][] = $typeReference;
            $transformedReference->referencedBy[$transformed] = $transformed->reference->getKey();

            $typeReference->connect($transformedReference);

            if (array_key_exists($typeReference->reference->getKey(), $transformed->missingReferences)) {
                unset($transformed->missingReferences[$typeReference->reference->getKey()]);
            }
        }, [TypeReference::class]);
    }
}
