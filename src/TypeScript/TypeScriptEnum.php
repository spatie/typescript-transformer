<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptEnum implements TypeScriptExportableNode, TypeScriptNode
{
    /**
     * @param string $name
     * @param array<int, array{name: string, value: string|int|null}> $cases
     */
    public function __construct(
        public string $name,
        public array $cases,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $output = 'enum '.$this->name.' {'.PHP_EOL;

        foreach ($this->cases as $case) {
            $output .= '    ';

            $output .= match (true) {
                is_int($case['value']) => "{$case['name']} = {$case['value']},",
                is_string($case['value']) => "{$case['name']} = '{$case['value']}',",
                default => "{$case['name']},",
            };

            $output .= PHP_EOL;
        }

        $output .= '}';

        return $output;
    }

    public function getExportedName(): string
    {
        return $this->name;
    }
}
