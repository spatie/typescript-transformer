<?php

namespace Spatie\TypeScriptTransformer\Formatters;

interface Formatter
{
    public function format(array $files): void;
}
