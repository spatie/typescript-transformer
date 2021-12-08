<?php

namespace Spatie\TypeScriptTransformer\Structures;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Spatie\TypeScriptTransformer\Exceptions\SymbolAlreadyExists;

class TypesCollection implements ArrayAccess, Countable, IteratorAggregate
{
    protected array $types = [];

    protected array $structure = [];

    public static function create(): self
    {
        return new self();
    }

    public function offsetExists($class): bool
    {
        return array_key_exists($class, $this->types);
    }

    public function offsetGet($class): ?TransformedType
    {
        return $this->types[$class] ?? null;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->types);
    }

    /**
     * @param null|string|\Spatie\TypeScriptTransformer\Structures\TransformedType $class
     * @param \Spatie\TypeScriptTransformer\Structures\TransformedType $type
     *
     * @throws \Spatie\TypeScriptTransformer\Exceptions\SymbolAlreadyExists
     */
    public function offsetSet($class, $type): void
    {
        $class ??= $type->reflection->getName();

        $class = $class instanceof TransformedType
            ? $class->reflection->getName()
            : $class;

        if (array_key_exists($class, $this->types) === false && $type->isInline === false) {
            $this->ensureTypeCanBeAdded($type);
        }

        $this->types[$class] = $type;
    }

    public function offsetUnset($class): void
    {
        unset($this->types[$class]);
    }

    public function count(): int
    {
        return count($this->types);
    }

    protected function ensureTypeCanBeAdded(TransformedType $type): void
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
