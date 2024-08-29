<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptObject implements TypeScriptNode, TypeScriptVisitableNode
{
    /**
     * @param  array<TypeScriptProperty>  $properties
     */
    public function __construct(
        public array $properties
    ) {
    }

    public function write(WritingContext $context): string
    {
        if (empty($this->properties)) {
            return 'object';
        }

        $output = '{'.PHP_EOL;

        foreach ($this->properties as $property) {
            $output .= $property->write($context).PHP_EOL;
        }

        return $output.'}';
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->iterable('properties');
    }
}
