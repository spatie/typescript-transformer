<?php

namespace Spatie\TypescriptTransformer;

use Exception;
use Illuminate\Support\Str;
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
        array_merge(
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

        if (Str::endsWith($annotations[1], '.ts')) {
            $file = $annotations[1];
        } else {
            $name = $annotations[1];
        }

        if (Str::endsWith($annotations[2], '.ts') && $name) {
            $file = $annotations[2];
        }

        if (empty($file)) {
            $file = $this->defaultFile;
        }

        if (empty($name)) {
            $name = class_basename($class->getName());
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

        if(! class_exists($class)){
            throw InvalidTransformerGiven::classDoesNotExist($class, $annotations[1]);
        }

        if(! $class instanceof Transformer){
            throw InvalidTransformerGiven::classIsNotATransformer($class, $annotations[1]);
        }

        return ['transformer' => $annotations[1]];
    }

    private function normalizeFilePath(string $file): string
    {
        $file = trim($file);

        if (Str::startsWith($file, '/')) {
            return substr($file, 1);
        }

        return $file;
    }
}
