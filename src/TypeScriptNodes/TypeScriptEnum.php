<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptEnum implements TypeScriptNamedNode, TypeScriptNode
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
        $output = 'enum '.$this->name.' {'."\n";

        foreach ($this->cases as $case) {
            $output .= '    ';

            $output .= match (true) {
                is_int($case['value']) => "{$case['name']} = {$case['value']},",
                is_string($case['value']) => "{$case['name']} = '{$case['value']}',",
                default => "{$case['name']},",
            };

            $output .= "\n";
        }

        $output .= '}';

        return $output;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
