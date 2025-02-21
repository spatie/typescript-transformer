<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Actions\FormatTypeScriptAction;
use Spatie\TypeScriptTransformer\Actions\PersistTypesCollectionAction;
use Spatie\TypeScriptTransformer\Actions\ResolveTypesCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Symfony\Component\Finder\Finder;

class TypeScriptTransformer
{
    protected TypeScriptTransformerConfig $config;

    private static $overriddenClasses = [];

    public static function override(string $className, string $newClassName): void {
        self::$overriddenClasses[$className] = $newClassName;
    }

    public static function make(string $className, ...$args) {
        $className = self::$overriddenClasses[$className] ?? $className;
        return new $className(...$args);
    }

    public static function create(TypeScriptTransformerConfig $config): self
    {
        return new self($config);
    }

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function transform(): TypesCollection
    {
        $typesCollection = self::make(
            ResolveTypesCollectionAction::class,
            new Finder(),
            $this->config,
        )->execute();

        self::make(PersistTypesCollectionAction::class, $this->config)->execute($typesCollection);

        self::make(FormatTypeScriptAction::class, $this->config)->execute();

        return $typesCollection;
    }
}
