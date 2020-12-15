<?php

namespace Spatie\TypeScriptTransformer\Structures;

use Spatie\TypeScriptTransformer\Transformers\Transformer;

class CollectedOccurrence
{
    public Transformer $transformer;

    public string $name;

    public static function create(Transformer $transformer, string $name): self
    {
        return new self($transformer, $name);
    }

    public function __construct(Transformer $transformer, string $name)
    {
        $this->transformer = $transformer;
        $this->name = $name;
    }
}
