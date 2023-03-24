<?php

namespace Spatie\TypeScriptTransformer\Transformers\New;

use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;

abstract class NewTransformer
{
    public function transform(
        mixed $reflection,
        array $annotations,
        ?string $alias = null,
        bool $inline = false
    ): ?Transformed
    {
        return $this->tryTransformation($reflection, $annotations);
    }

    abstract protected function tryTransformation(
        mixed $reflection,
        array $annotations,
        ?string $alias = null,
        bool $inline = false
    ): ?Transformed;
}
