<?php

namespace Spatie\TypeScriptTransformer\References;

use ReflectionClass;

class ReflectionClassReference extends ClassStringReference
{
    public function __construct(
        public ReflectionClass $reflectionClass,
    ) {
        parent::__construct($reflectionClass->getName());
    }
}
