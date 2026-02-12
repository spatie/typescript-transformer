<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\Visitor\Visitor;

class CollectAdditionalImportsAction
{
    protected Visitor $visitor;

    public function __construct()
    {
        $this->visitor = $this->resolveVisitor();
    }

    public function execute(TransformedCollection $collection): void
    {
        foreach ($collection->onlyChanged() as $transformed) {
            $metadata = [
                'transformed' => $transformed,
            ];

            $this->visitor->execute($transformed->typeScriptNode, $metadata);
        }
    }

    protected function resolveVisitor(): Visitor
    {
        return Visitor::create()->before(function (TypeScriptRaw $raw, array &$metadata) {
            if ($raw->additionalImports === []) {
                return;
            }

            /** @var Transformed $transformed */
            $transformed = $metadata['transformed'];

            foreach ($raw->additionalImports as $import) {
                $transformed->additionalImports[] = $import;
            }
        }, [TypeScriptRaw::class]);
    }
}
