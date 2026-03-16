<?php

namespace Spatie\TypeScriptTransformer\TypeResolvers\Data;

class ParsedClass
{
    /**
     * @param  array<ParsedNameAndType>  $properties
     * @param  array<string>  $templates
     */
    public function __construct(
        public array $properties,
        public array $templates = [],
    ) {
    }
}
