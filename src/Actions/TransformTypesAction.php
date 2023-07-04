<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TransformTypesAction
{
    protected TransformTypeAction $transformTypeAction;

    public function __construct(
        protected TypeScriptTransformerConfig $config,
        protected TypeScriptTransformerLog $log,
    ) {
        $this->transformTypeAction = new TransformTypeAction($config, $log);
    }

    /**
     * @param array<string> $types
     */
    public function execute(array $types): TransformedCollection
    {
        $collection = new TransformedCollection();

        foreach ($types as $type) {
            $transformed = $this->transformTypeAction->execute($type);

            if ($transformed) {
                $collection->add($transformed);
            }
        }

        return $collection;
    }
}
