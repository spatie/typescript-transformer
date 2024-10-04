<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionAttribute;
use ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionAttribute as RoaveReflectionAttribute;

class PhpAttributeNode
{
    protected ?array $arguments = null;

    public function __construct(
        public readonly ReflectionAttribute|RoaveReflectionAttribute $reflection
    ) {
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getArguments(): array
    {
        return $this->reflection->getArguments();
    }

    public function hasArgument(string $name): bool
    {
        if ($this->arguments === null) {
            $this->initializeArguments();
        }

        return array_key_exists($name, $this->arguments);
    }

    public function getArgument(string $name): mixed
    {
        if ($this->arguments === null) {
            $this->initializeArguments();
        }

        return $this->arguments[$name] ?? null;
    }

    public function newInstance(): object
    {
        if ($this->reflection instanceof ReflectionAttribute) {
            return $this->reflection->newInstance();
        }

        $className = $this->reflection->getName();

        // TODO: maybe we can do a little better here
        return (new $className())($this->reflection->getArguments());
    }

    /** @return array<string, mixed> */
    protected function initializeArguments(): array
    {
        // TODO: this is a quickly written thing, test it to be sure it works
        if ($this->arguments !== null) {
            return $this->arguments;
        }

        $this->arguments = [];

        $values = $this->getArguments();

        foreach ($values as $name => $value) {
            if (is_string($name)) {
                $this->arguments[$name] = $value;
                unset($values[$name]);
            }
        }

        if (count($values) === 0) {
            return $this->arguments;
        }

        $constructor = new ReflectionMethod($this->reflection->getName(), '__construct');

        foreach ($constructor->getParameters() as $index => $param) {
            if(array_key_exists($param->getName(), $this->arguments)) {
                continue;
            }

            if(! array_key_exists($index, $values)) {
                continue;
            }

            $this->arguments[$param->getName()] = $values[$index];
        }

        return $this->arguments;
    }
}
