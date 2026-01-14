<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptImport implements TypeScriptNode
{
    /**
     * @param  array<array{name: string, alias?: ?string}>  $segments
     */
    public function __construct(
        public string $path,
        public array $segments,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $segments = implode(', ', array_map(
            function (array $name) {
                $alias = $name['alias'] ?? null;

                return $alias
                    ? "{$name['name']} as {$alias}"
                    : $name['name'];
            },
            $this->segments,
        ));

        return "import { {$segments} } from '{$this->path}';";
    }
}
