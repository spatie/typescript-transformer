<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\Loggers\Logger;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\Visitor\Visitor;

class ConnectReferencesAction
{
    protected Visitor $visitor;

    public function __construct(
        protected Logger $log,
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

            $this->visitor->execute($transformed->getNode(), $metadata);
        }
    }

    protected function resolveVisitor(): Visitor
    {
        return Visitor::create()->before(function (TypeScriptReference $typeReference, array &$metadata) {
            /** @var Transformed $currentTransformed */
            $currentTransformed = $metadata['transformed'];

            /** @var TransformedCollection $collection */
            $collection = $metadata['collection'];

            $foundTransformed = $collection->get($typeReference->reference);

            if ($foundTransformed === null) {
                $currentTransformed->addMissingReference($typeReference->reference, $typeReference);

                $this->log->warning("Tried replacing reference to `{$typeReference->reference->humanFriendlyName()}` in `{$currentTransformed->getReference()->humanFriendlyName()}` but it was not found in the transformed types");

                return;
            }

            $currentTransformed->references($foundTransformed->getReference()->getKey(), $typeReference);
            $foundTransformed->referencedBy($currentTransformed->getReference()->getKey());

            $typeReference->connect($foundTransformed);

            $currentTransformed->removeMissingReference($foundTransformed->getReference()->getKey());
        }, [TypeScriptReference::class]);
    }
}
