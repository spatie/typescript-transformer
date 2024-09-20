<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionAttribute;
use Roave\BetterReflection\Reflection\ReflectionAttribute as RoaveReflectionAttribute;

class PhpAttributeNode
{
    public function __construct(
        public readonly ReflectionAttribute|RoaveReflectionAttribute $reflection
    ) {
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getArguments(): array
    {
        return $this->reflection->getArguments();
    }

    public function newInstance(): object
    {
        if($this->reflection instanceof ReflectionAttribute) {
            return $this->reflection->newInstance();
        }

        $className = $this->reflection->getName();

        // TODO: maybe we can do a little better here
        return (new $className())($this->reflection->getArguments());
    }
}
