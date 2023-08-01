<?php

namespace Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;

interface ClassPropertyProcessor
{
    public function execute(
        ReflectionProperty $reflection,
        ?TypeNode $annotation,
        TypeScriptProperty $property
    ): ?TypeScriptProperty;
}
