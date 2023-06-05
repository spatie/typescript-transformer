<?php

namespace Spatie\TypeScriptTransformer\Exceptions;

class FuzzySearchFailed extends \Exception
{
    public static function create(string $shortName): self
    {
        return new self("There is more then one Type with short name '{$shortName}'. Use FQN in phpdoc to avoid this.");
    }
}
