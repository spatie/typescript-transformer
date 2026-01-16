<?php

namespace Spatie\TypeScriptTransformer\References;

class ClassStringReference implements Reference
{
    public string $classString;

    public function __construct(
        string $classString
    ) {
        $this->classString = trim($classString, '\\');
    }

    public function getKey(): string
    {
        return "class_string_{$this->classString}";
    }

    public function humanFriendlyName(): string
    {
        return "class {$this->classString}";
    }
}
