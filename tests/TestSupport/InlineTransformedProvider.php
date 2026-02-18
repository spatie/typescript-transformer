<?php

namespace Spatie\TypeScriptTransformer\Tests\TestSupport;

use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProvider;

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

    public function provide(): array
    {
        return $this->transformed;
    }
}
