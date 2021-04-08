<?php

namespace Spatie\TypeScriptTransformer;

use ReflectionClass;

class ClassReader
{
    public function forClass(ReflectionClass $class): array
    {
        return [
            'transformable' => $this->resolveTransformable($class),
            'name' => $this->resolveName($class),
            'transformer' => $this->resolveTransformer($class),
            'inline' => $this->resolveInline($class),
        ];
    }

    protected function resolveTransformable(ReflectionClass $class): bool
    {
        return str_contains($class->getDocComment(), '@typescript');
    }

    protected function resolveName(ReflectionClass $class): string
    {
        $annotations = [];

        preg_match(
            '/@typescript\s*([\w\/\.]*)\s*/',
            $class->getDocComment(),
            $annotations
        );

        $name = $annotations[1] ?? null;

        if (count($annotations) !== 2 || empty($name)) {
            return $class->getShortName();
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

        return $annotations[1];
    }

    private function resolveInline(ReflectionClass $class): bool
    {
        return str_contains($class->getDocComment(), '@typescript-inline');
    }
}
