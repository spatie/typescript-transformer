<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionAttribute;
use ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionAttribute as RoaveReflectionAttribute;

class PhpAttributeNode
{
    protected ?array $namedArguments = null;

    public function __construct(
        public readonly ReflectionAttribute|RoaveReflectionAttribute $reflection
    ) {
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getRawArguments(): array
    {
        return $this->reflection->getArguments();
    }

    public function hasArgument(string $name): bool
    {
        if ($this->namedArguments === null) {
            $this->initializeNamedArguments();
        }

        return array_key_exists($name, $this->namedArguments);
    }

    public function getArgument(string $name): mixed
    {
        if ($this->namedArguments === null) {
            $this->initializeNamedArguments();
        }

        return $this->namedArguments[$name] ?? null;
    }

    public function newInstance(): object
    {
        if ($this->reflection instanceof ReflectionAttribute) {
            return $this->reflection->newInstance();
        }

        $className = $this->reflection->getName();

        $this->initializeNamedArguments();

        return (new $className())(...$this->namedArguments);
    }

    /** @return array<string, mixed> */
    protected function initializeNamedArguments(): array
    {
        if ($this->namedArguments !== null) {
            return $this->namedArguments;
        }

        $this->namedArguments = [];

        $values = $this->getRawArguments();

        $constructor = new ReflectionMethod($this->reflection->getName(), '__construct');

        $parameters = $constructor->getParameters();

        $namedParametersMap = array_flip(array_map(
            fn ($param) => $param->getName(),
            $parameters
        ));

        foreach ($values as $name => $value) {
            if (is_int($name)) {
                $parameter = $parameters[$name];

                if ($parameter->isVariadic()) {
                    $this->namedArguments[$parameter->name] = array_values(array_slice(
                        $values,
                        $name,
                        null,
                        true
                    ));

                    return $this->namedArguments;
                }

                $name = $parameters[$name]->getName();
            }

            $this->namedArguments[$name] = $value;

            unset($parameters[$namedParametersMap[$name]]);
        }

        foreach ($parameters as $parameter) {
            $this->namedArguments[$parameter->getName()] = $parameter->getDefaultValue();
        }


        return $this->namedArguments;
    }
}
