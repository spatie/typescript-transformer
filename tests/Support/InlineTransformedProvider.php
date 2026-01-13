<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class InlineTransformedProvider implements TransformedProvider
{
    /** @var Transformed[] */
    protected array $transformed;

    public function __construct(
        array|Transformed|TransformedFactory $transformed,
    ) {
        $this->transformed = is_array($transformed) ? $transformed : [$transformed];

        foreach ($this->transformed as $key => $transformed) {
            if ($transformed instanceof TransformedFactory) {
                $this->transformed[$key] = $transformed->build();
            }
        }
    }

    public function provide(TypeScriptTransformerConfig $config, TransformedCollection $types): void
    {
        foreach ($this->transformed as $transformed) {
            $types->add($transformed);
        }
    }
}
