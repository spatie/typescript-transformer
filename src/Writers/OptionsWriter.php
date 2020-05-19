<?php

namespace Spatie\TypescriptTransformer\Writers;

use Spatie\TypescriptTransformer\Type;

class OptionsWriter implements Writer
{
    public function persist(Type $type): string
    {
        $tsOptions = implode(' | ', array_map(
            fn (string $option) => "'{$option}'",
            $type->options
        ));

        return "export type {$type->name} = {$tsOptions};";
    }
}
