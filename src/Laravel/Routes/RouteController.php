<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;

class RouteController implements RouterStructure
{
    /**
     * @param  array<string, RouteControllerAction>  $actions
     */
    public function __construct(
        public array $actions,
    ) {
    }

    public function toTypeScriptNode(): TypeScriptNode
    {
        return new TypeScriptObject([
            new TypeScriptProperty('actions', new TypeScriptObject(collect($this->actions)->map(function (RouteControllerAction $controller, string $name) {
                return new TypeScriptProperty(
                    $name,
                    $controller->toTypeScriptNode(),
                );
            })->all())),
        ]);
    }

    public function toJsObject(): array
    {
        return [
            'actions' => collect($this->actions)->map(function (RouteControllerAction $action, string $name) {
                return [
                    $name => $action->toJsObject(),
                ];
            })->all(),
        ];
    }
}
