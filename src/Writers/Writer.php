<?php

namespace Spatie\TypescriptTransformer\Writers;

use Spatie\TypescriptTransformer\Type;

interface Writer
{
    public function persist(Type $type): string;
}
