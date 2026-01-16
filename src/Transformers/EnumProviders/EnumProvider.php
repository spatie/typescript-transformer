<?php

namespace Spatie\TypeScriptTransformer\Transformers\EnumProviders;

use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;

interface EnumProvider
{
    public function isEnum(PhpClassNode $phpClassNode): bool;

    public function isValidUnion(PhpClassNode $phpClassNode): bool;

    public function resolveCases(PhpClassNode $phpClassNode): array;
}
