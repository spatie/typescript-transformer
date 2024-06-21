<?php

namespace Spatie\TypeScriptTransformer\TypeResolvers\Data;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;

class ParsedMethod
{
    /**
     * @param array<string,ParsedNameAndType> $parameters
     */
    public function __construct(
        public array $parameters,
        public ?TypeNode $returnType,
    ) {
    }
}
