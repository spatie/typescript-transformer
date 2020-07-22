<?php

namespace Spatie\TypescriptTransformer\Structures;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Spatie\TypescriptTransformer\Exceptions\SymbolAlreadyExists;

class TypesCollection implements ArrayAccess, Countable, IteratorAggregate
{
    private array $types = [];

    private array $structure = [];

    public static function create(): self
    {
        return new self();
    }

    public function offsetExists($class): bool
    {
        return array_key_exists($class, $this->types);
    }

    public function offsetGet($class): ?Type
    {
        return $this->types[$class] ?? null;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->types);
    }

    public function offsetSet($class, $type): void
    {
        $class ??= $type->reflection->getName();

        $class = $class instanceof Type
            ? $class->reflection->getName()
            : $class;

        if (! array_key_exists($class, $this->types)) {
            $this->ensureTypeCanBeAdded($type);
        }

        $this->types[$class] = $type;
    }

    public function offsetUnset($class): void
    {
        unset($this->types[$class]);
    }

    public function count()
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
