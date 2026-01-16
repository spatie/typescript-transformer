<?php

namespace Spatie\TypeScriptTransformer\TypeResolvers\Data;

class ParsedClass
{
    /**
     * @param  array<ParsedNameAndType>  $properties
     */
    public function __construct(
        public array $properties,
    ) {
    }
}
