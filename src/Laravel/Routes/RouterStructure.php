<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

interface RouterStructure
{
    public function toJsObject(): array;
}
