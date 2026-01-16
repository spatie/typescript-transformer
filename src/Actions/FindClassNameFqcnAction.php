<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\StructureDiscoverer\Collections\UsageCollection;
use Spatie\StructureDiscoverer\Support\UseDefinitionsResolver;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;

class FindClassNameFqcnAction
{
    /** @var array<string, UsageCollection> */
    protected static array $cache = [];

    public function __construct(
        protected UseDefinitionsResolver $useDefinitionsResolver = new UseDefinitionsResolver()
    ) {
    }

    public function execute(PhpClassNode $node, string $className): ?string
    {
        $usages = $this->loadUsages($node);

        $className = $this->cleanupClassname($className);

        if ($usage = $usages->findForAlias($className)) {
            return $this->cleanupClassname($usage->fcqn);
        }

        if (! $node->inNamespace() && class_exists($className)) {
            return $this->cleanupClassname($className);
        }

        $guessedFqcn = "{$node->getNamespaceName()}\\{$className}";

        if (class_exists($guessedFqcn)) {
            return $this->cleanupClassname($guessedFqcn);
        }

        return $className;
    }

    protected function loadUsages(PhpClassNode $node): UsageCollection
    {
        $filename = $node->getFileName();

        if (! array_key_exists($filename, static::$cache)) {
            static::$cache[$filename] = $this->useDefinitionsResolver->execute($filename);
        }

        return static::$cache[$filename];
    }

    protected function cleanupClassname(
        string $classname
    ): string {
        return ltrim($classname, '\\');
    }
}
