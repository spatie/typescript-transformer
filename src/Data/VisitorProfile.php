<?php

namespace Spatie\TypeScriptTransformer\Data;

class VisitorProfile
{
    public static function create(): self
    {
        return new self();
    }

    public function __construct(
        public array $singleNodes = [],
        public array $iterableNodes = [],
    ) {
    }

    public function single(string ...$nodes): self
    {
        array_push($this->singleNodes, ...$nodes);

        return $this;
    }

    public function iterable(string ...$nodes): self
    {
        array_push($this->iterableNodes, ...$nodes);

        return $this;
    }
}
