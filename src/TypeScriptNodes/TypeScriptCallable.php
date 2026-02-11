<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptCallable implements TypeScriptNode
{
    /**
     * @param  array<TypeScriptParameter>  $parameters
     */
    public function __construct(
        #[NodeVisitable]
        public array $parameters = [],
        #[NodeVisitable]
        public ?TypeScriptNode $returnType = null,
    ) {
    }

    public function write(WritingContext $context): string
    {
        if (empty($this->parameters) && $this->returnType === null) {
            return '(...args: any[]) => any';
        }

        $params = implode(', ', array_map(
            fn (TypeScriptNode $param) => $param->write($context),
            $this->parameters
        ));

        $returnType = $this->returnType?->write($context) ?? 'any';

        return "({$params}) => {$returnType}";
    }
}
