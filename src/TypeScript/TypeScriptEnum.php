<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptEnum implements TypeScriptNode, TypeScriptExportableNode
{
    public function __construct(
        public string $name,
        public array $cases,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $output = 'export enum '.$this->name.' {'.PHP_EOL;

        foreach ($this->cases as $case) {
            $output .= '    '.$case.','.PHP_EOL;
        }

        $output .= '}'.PHP_EOL;

        return $output;
    }

    public function getExportedName(): string
    {
        return $this->name;
    }
}
