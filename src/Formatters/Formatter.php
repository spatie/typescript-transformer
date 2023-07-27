<?php

namespace Spatie\TypeScriptTransformer\Formatters;

interface Formatter
{
    public function format(string $file): void;
}
