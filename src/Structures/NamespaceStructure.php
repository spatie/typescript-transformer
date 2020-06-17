<?php

namespace Spatie\TypescriptTransformer\Structures;

use Exception;

class NamespaceStructure
{
    /** @var array|\Spatie\TypescriptTransformer\Structures\NamespaceStructure[] */
    private array $namespaces = [];

    /** @var array|\Spatie\TypescriptTransformer\Structures\Type[] */
    private array $types = [];

    private ?string $name;

    public function __construct(?string $name)
    {
        $this->name = $name;
    }

    public function add(array $namespaceSegments, Type $type)
    {
        count($namespaceSegments) === 0
            ? $this->addType($type)
            : $this->addNamespace($namespaceSegments, $type);

        return $this;
    }

    public function find(array $segments): ?Type
    {
        if(count($segments) === 1){
            return $this->types[array_shift($segments)] ?? null;
        }

        $namespace =  $this->namespaces[array_shift($segments)] ?? null;

        if($namespace === null){
            return null;
        }

        return $namespace->find($segments);
    }

    /**
     * @return array|\Spatie\TypescriptTransformer\Structures\NamespaceStructure[]
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * @return array|\Spatie\TypescriptTransformer\Structures\Type[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    private function addType(Type $type)
    {
        if (array_key_exists($type->name, $this->namespaces)) {
            throw new Exception("Could not add type {$type->name}({$type->reflection->getName()}) because it collides with namespace {$this->name}");
        }

        if (array_key_exists($type->name, $this->types)) {
            $existingType = $this->types[$type->name];

            throw new Exception("Could not add type {$type->name} because a type {$existingType->name}({$existingType->reflection->getName()}) already exists");
        }

        $this->types[$type->name] = $type;
    }

    private function addNamespace(array $namespaceSegments, Type $type)
    {
        $segment = array_shift($namespaceSegments);

        if (array_key_exists($segment, $this->types)) {
            $existingType = $this->types[$segment];

            throw new Exception("Could not add namespace {$segment} because a type {$existingType->name}({$existingType->reflection->getName()}) already exists");
        }

        if (array_key_exists($segment, $this->namespaces)) {
            $this->namespaces[$segment]->add($namespaceSegments, $type);

            return;
        }

        $this->namespaces[$segment] = new NamespaceStructure($segment);
        $this->namespaces[$segment]->add($namespaceSegments, $type);

        return;
    }
}
