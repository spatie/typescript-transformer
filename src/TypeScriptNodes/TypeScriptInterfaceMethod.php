<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptInterfaceMethod implements TypeScriptNode, TypeScriptVisitableNode
{
    /**
     * @param  array<TypeScriptParameter>  $parameters
     */
    public function __construct(
        public string $name,
        public array $parameters,
        public TypeScriptNode $returnType,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $parameters = implode(', ', array_map(
            fn (TypeScriptParameter $parameter) => $parameter->write($context),
            $this->parameters
        ));

        return "{$this->name}({$parameters}): {$this->returnType->write($context)};";
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->iterable('parameters')->single('returnType');
    }
}
