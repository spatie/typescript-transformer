<?php

namespace Spatie\TypeScriptTransformer;

use Exception;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Exceptions\InvalidTransformerGiven;
use Spatie\TypeScriptTransformer\Transformers\Transformer;

class ClassReader
{
    public function forClass(ReflectionClass $class): array
    {
        return [
            'name' => $this->resolveName($class),
            'transformer' => $this->resolveTransformer($class),
        ];
    }

    protected function resolveName(ReflectionClass $class): string
    {
        $annotations = [];

        preg_match(
            '/@typescript\s*([\w\/\.]*)\s*/',
            $class->getDocComment(),
            $annotations
        );

        if (count($annotations) !== 2) {
            throw new Exception("Wrong TypeScript definition in {$class->getName()}");
        }

        $name = $annotations[1];

        if (empty($name)) {
            $name = $class->getShortName();
        }

        return $name;
    }

    protected function resolveTransformer(ReflectionClass $class): ?string
    {
        $annotations = [];

        preg_match(
            '/@typescript-transformer\s+([\w\\\]*)/',
            $class->getDocComment(),
            $annotations
        );

        if (count($annotations) !== 2 || empty($annotations[1])) {
            return null;
        }

        $transformerClass = $annotations[1];

        if (! class_exists($transformerClass)) {
            throw InvalidTransformerGiven::classDoesNotExist($class, $transformerClass);
        }

        if (! is_subclass_of($transformerClass, Transformer::class)) {
            throw InvalidTransformerGiven::classIsNotATransformer($class, $transformerClass);
        }

        return $transformerClass;
    }
}
