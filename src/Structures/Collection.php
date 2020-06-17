<?php

namespace Spatie\TypescriptTransformer\Structures;

use Closure;

class Collection
{
    private NamespaceStructure $structure;

    private array $types = [];

    public static function create(): self
    {
        return new self();
    }

    public function __construct()
    {
        $this->structure = new NamespaceStructure(null);
    }

    public function add(Type $type): self
    {
        $this->structure->add(
            $type->getNamespaceSegments(),
            $type
        );

        $this->types[$type->reflection->getName()] = $type;

        return $this;
    }

    public function find(string $class): ?Type
    {
//        return $this->structure->find(explode('\\', $class));

        return $this->types[$class] ?? null;
    }

    public function map(Closure $closure)
    {
        $this->types = array_map($closure, $this->types);
    }

    public function getStructure(): NamespaceStructure
    {
        return $this->structure;
    }

    /**
     * @return array|\Spatie\TypescriptTransformer\Structures\Type[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
