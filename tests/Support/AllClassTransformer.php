<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;

class AllClassTransformer extends ClassTransformer
{
    protected function shouldTransform(PhpClassNode $phpClassNode): bool
    {
        return true;
    }
}
