<?php

namespace Spatie\TypescriptTransformer\Transformers;

abstract class InlineTransformer extends Transformer
{
    public function isInline(): bool
    {
        return true;
    }
}
