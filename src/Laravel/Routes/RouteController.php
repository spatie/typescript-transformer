<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

class RouteController implements RouterStructure
{
    /**
     * @param array<string, RouteControllerAction> $actions
     */
    public function __construct(
        public array $actions,
    ) {
    }

    public function toJsObject(): array
    {
        return [
            'actions' => collect($this->actions)->map(fn (RouteControllerAction $action, string $name) => $action->toJsObject())->all(),
        ];
    }
}
