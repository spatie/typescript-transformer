<?php

namespace Spatie\TypeScriptTransformer\TypeResolvers\Data;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;

class ParsedNameAndType
{
    public function __construct(
        public string $name,
        public TypeNode $type,
    ) {
    }
}
