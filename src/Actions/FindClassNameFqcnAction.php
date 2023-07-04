<?php

namespace Spatie\TypeScriptTransformer\Actions;

use ReflectionClass;
use Spatie\StructureDiscoverer\Collections\UsageCollection;

class FindClassNameFqcnAction
{
    /** @var array<string, UsageCollection> */
    protected static array $cache = [];

    public function execute(ReflectionClass $reflectionClass, string $className): ?string
    {
        $usages = $this->loadUsages($reflectionClass);

        $className = $this->cleanupClassname($className);

        if ($usage = $usages->findForAlias($className)) {
            return $this->cleanupClassname($usage->fcqn);
        }

        if (! $reflectionClass->inNamespace() && class_exists($className)) {
            return $this->cleanupClassname($className);
        }

        $guessedFqcn = "{$reflectionClass->getNamespaceName()}\\{$className}";

        if(class_exists($guessedFqcn)){
            return $this->cleanupClassname($guessedFqcn);
        }

        return $className;
    }

    protected function loadUsages(ReflectionClass $reflectionClass): UsageCollection
    {
        $filename = $reflectionClass->getFileName();

        if (! array_key_exists($filename, static::$cache)) {
            static::$cache[$filename] = (new ParseUseDefinitionsAction())->execute($filename);
        }

        return static::$cache[$filename];
    }

    protected function cleanupClassname(
        string $classname
    ):string
    {
        return ltrim($classname, '\\');
    }
}
