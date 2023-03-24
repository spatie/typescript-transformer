<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use Spatie\TypeScriptTransformer\Transformers\DtoTransformer as BaseDtoTransformer;
use Spatie\TypeScriptTransformer\TypeProcessors\LaravelCollectionTypeProcessor;
use Spatie\TypeScriptTransformer\TypeProcessors\ReplaceDefaultsTypeProcessor;

class LaravelDtoTransformer extends BaseDtoTransformer
{
    protected function typeProcessors(): array
    {
        return [
            new ReplaceDefaultsTypeProcessor(
                $this->config->getDefaultTypeReplacements()
            ),
            new LaravelCollectionTypeProcessor(),
        ];
    }
}
