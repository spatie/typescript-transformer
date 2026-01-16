<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Transformers\InterfaceTransformer;

class AllInterfaceTransformer extends InterfaceTransformer
{
    protected function shouldTransform(PhpClassNode $phpClassNode): bool
    {
        return true;
    }
}
