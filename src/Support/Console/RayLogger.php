<?php

namespace Spatie\TypeScriptTransformer\Support\Console;

class RayLogger implements Logger
{
    public function error(mixed $item, ?string $title = null): void
    {
        $this->sendToRay($item, $title, 'red');
    }

    public function info(mixed $item, ?string $title = null): void
    {
        $this->sendToRay($item, $title, 'blue');
    }

    public function warn(mixed $item, ?string $title = null): void
    {
        $this->sendToRay($item, $title, 'orange');
    }

    public function debug(mixed $item, ?string $title = null): void
    {
        $this->sendToRay($item, $title, 'gray');
    }

    protected function sendToRay(
        mixed $item,
        ?string $title,
        string $color,
    ) {
        $ray = ray($item)->color($color);

        if ($title) {
            $ray->label($title);
        }
    }
}
