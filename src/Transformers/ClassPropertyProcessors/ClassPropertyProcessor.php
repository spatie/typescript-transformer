<?php

namespace Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpPropertyNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;

interface ClassPropertyProcessor
{
    public function execute(
        PhpPropertyNode $phpPropertyNode,
        ?TypeNode $annotation,
        TypeScriptProperty $property
    ): ?TypeScriptProperty;
}
