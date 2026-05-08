<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes\Concerns;

trait OutputsTypeScriptLiteral
{
    protected function outputLiteral(int|string|float|bool|null $value): string
    {
        if (is_string($value)) {
            $escaped = strtr($value, [
                '\\' => '\\\\',
                "'" => "\\'",
                "\n" => '\\n',
                "\r" => '\\r',
                "\t" => '\\t',
            ]);

            return "'{$escaped}'";
        }

        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
