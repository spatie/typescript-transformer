<?php

namespace Spatie\TypescriptTransformer;

use Exception;
use Illuminate\Support\Str;
use ReflectionClass;

class ClassReader
{
    private string $defaultFile;

    public function __construct(string $defaultFile)
    {
        $this->defaultFile = $defaultFile;
    }

    public function forClass(ReflectionClass $class): array
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
        $type = null;

        if (Str::endsWith($annotations[1], '.ts')) {
            $file = $annotations[1];
        } else {
            $type = $annotations[1];
        }

        if (Str::endsWith($annotations[2], '.ts') && $type) {
            $file = $annotations[2];
        }

        if (empty($file)) {
            $file = $this->defaultFile;
        }

        if (empty($type)) {
            $type = class_basename($class->getName());
        }

        $file = $this->normalizeFilePath($file);

        return ['file' => $file, 'type' => $type];
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
