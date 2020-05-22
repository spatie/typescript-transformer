<?php

namespace Spatie\TypescriptTransformer;

use Exception;
use ReflectionClass;
use Spatie\TypescriptTransformer\Exceptions\InvalidTransformerGiven;
use Spatie\TypescriptTransformer\Transformers\Transformer;

class ClassReader
{
    private string $defaultFile;

    public function __construct(string $defaultFile)
    {
        $this->defaultFile = $defaultFile;
    }

    public function forClass(ReflectionClass $class): array
    {
        return array_merge(
            $this->resolveFileAndNameProperty($class),
            $this->resolveTransformer($class),
        );
    }

    private function resolveFileAndNameProperty(ReflectionClass $class): array
    {
        $annotations = [];

        preg_match(
            '/@typescript\s*([\w\/\.]*)\s*([\w\/\.]*)/',
            $class->getDocComment(),
            $annotations
        );

        if (count($annotations) !== 3) {
            throw new Exception("Wrong typescript definition in {$class->getName()}");
        }

        $file = null;
        $name = null;

        if (substr($annotations[1], -3) === '.ts') {
            $file = $annotations[1];
        } else {
            $name = $annotations[1];
        }

        if (substr($annotations[2], -3) === '.ts' && $name) {
            $file = $annotations[2];
        }

        if (empty($file)) {
            $file = $this->defaultFile;
        }

        if (empty($name)) {
            $name = $class->getShortName();
        }

        $file = $this->normalizeFilePath($file);

        return ['file' => $file, 'name' => $name];
    }

    private function resolveTransformer(ReflectionClass $class): array
    {
        $annotations = [];

        preg_match(
            '/@typescript-transformer\s+([\w\\\]*)/',
            $class->getDocComment(),
            $annotations
        );

        if (count($annotations) !== 2 || empty($annotations[1])) {
            return ['transformer' => null];
        }

        $transformerClass = $annotations[1];

        if (! class_exists($transformerClass)) {
            throw InvalidTransformerGiven::classDoesNotExist($class, $transformerClass);
        }

        if (! is_subclass_of($transformerClass, Transformer::class)) {
            throw InvalidTransformerGiven::classIsNotATransformer($class, $transformerClass);
        }

        return ['transformer' => $transformerClass];
    }

    private function normalizeFilePath(string $file): string
    {
        $file = trim($file);

        if (substr($file, 0, 1) === '/') {
            return substr($file, 1);
        }

        return $file;
    }
}
