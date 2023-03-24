<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

class TypeScriptProperty extends TypeScriptParameter
{

    public function __toString()
    {
        return parent::__toString().';';
    }
}
