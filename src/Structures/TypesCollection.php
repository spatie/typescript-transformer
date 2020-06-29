<?php

namespace Spatie\TypescriptTransformer\Structures;

use Closure;
use Exception;
use Spatie\TypescriptTransformer\Exceptions\SymbolAlreadyExists;

class TypesCollection
{
    private array $types = [];

    private array $structure = [];

    public static function create(): self
    {
        return new self();
    }

    public function add(Type $type): self
    {
        $this->ensureTypeCanBeAdded($type);

        $this->types[$type->reflection->getName()] = $type;

        return $this;
    }

    public function find(string $class): ?Type
    {
        return $this->types[$class] ?? null;
    }

    public function replace(Type $type): self
    {
        $namespace = $type->reflection->getName();

        if (! array_key_exists($namespace, $this->types)) {
            throw new Exception("Tried replacing unknown type {$namespace}");
        }

        $this->types[$namespace] = $type;

        return $this;
    }

    public function map(Closure $closure)
    {
        $this->types = array_map($closure, $this->types);
    }

    /**
     * @return array|\Spatie\TypescriptTransformer\Structures\Type[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function count(): int
    {
        return count($this->types);
    }

    private function ensureTypeCanBeAdded(Type $type)
    {
        $namespace = array_reduce($type->getNamespaceSegments(), function (array $checkedSegments, string $segment) {
            $segments = array_merge($checkedSegments, [$segment]);

            $namespace = join('.', $segments);

            if (array_key_exists($namespace, $this->structure)) {
                if ($this->structure[$namespace]['kind'] !== 'namespace') {
                    throw SymbolAlreadyExists::whenAddingNamespace(
                        $namespace,
                        $this->structure[$namespace]
                    );
                }
            }

            $this->structure[$namespace] = [
                'kind' => 'namespace',
                'value' => str_replace('.', '\\', $namespace),
            ];

            return $segments;
        }, []);

        $namespacedType = join('.', array_merge($namespace, [$type->name]));

        if (array_key_exists($namespacedType, $this->structure)) {
            throw SymbolAlreadyExists::whenAddingType(
                $type->reflection->getName(),
                $this->structure[$namespacedType]
            );
        }

        $this->structure[$namespacedType] = [
            'kind' => 'type',
            'value' => $type->reflection->getName(),
        ];
    }
}
