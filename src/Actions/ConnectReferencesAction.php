<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
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
     * @param TransformedCollection $collection
     */
    public function execute(TransformedCollection $collection): void
    {
        foreach ($collection->onlyChanged() as $transformed) {
            $metadata = [
                'transformed' => $transformed,
                'collection' => $collection,
            ];

            $this->visitor->execute($transformed->typeScriptNode, $metadata);
        }
    }

    protected function resolveVisitor(): Visitor
    {
        return Visitor::create()->before(function (TypeReference $typeReference, array &$metadata) {
            /** @var Transformed $currentTransformed */
            $currentTransformed = $metadata['transformed'];

            /** @var TransformedCollection $collection */
            $collection = $metadata['collection'];

            $foundTransformed = $collection->get($typeReference->reference);

            if ($foundTransformed === null) {
                $currentTransformed->addMissingReference($typeReference->reference, $typeReference);

                $this->log->warning("Tried replacing reference to `{$typeReference->reference->humanFriendlyName()}` in `{$currentTransformed->reference->humanFriendlyName()}` but it was not found in the transformed types");

                return;
            }

            if (! array_key_exists($foundTransformed->reference->getKey(), $currentTransformed->references)) {
                $currentTransformed->references[$foundTransformed->reference->getKey()] = [];
            }

            $currentTransformed->references[$foundTransformed->reference->getKey()][] = $typeReference;
            $foundTransformed->referencedBy[] = $currentTransformed->reference->getKey();

            $typeReference->connect($foundTransformed);

            if (array_key_exists($foundTransformed->reference->getKey(), $currentTransformed->missingReferences)) {
                unset($currentTransformed->missingReferences[$foundTransformed->reference->getKey()]);
            }
        }, [TypeReference::class]);
    }
}
