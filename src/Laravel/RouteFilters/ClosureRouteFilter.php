<?php

namespace Spatie\TypeScriptTransformer\Laravel\RouteFilters;

use Closure;
use Illuminate\Routing\Route;
use Laravel\SerializableClosure\SerializableClosure;

class ClosureRouteFilter implements RouteFilter
{
    /**
     * @param Closure(Route):bool $closure
     */
    public function __construct(
        protected Closure $closure
    ) {
    }

    public function hide(Route $route): bool
    {
        return ($this->closure)($route);
    }

    public function __serialize(): array
    {
        return [
            'closure' => serialize(new SerializableClosure($this->closure)),
        ];
    }

    public function __unserialize(array $data): void
    {
        /** @var SerializableClosure $serializableClosure */
        $serializableClosure = unserialize($data['closure']);

        $this->closure = $serializableClosure->getClosure();
    }
}
