<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\WatchEvent;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\WatchingTransformedProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class InlineWatchingTransformedProvider implements TransformedProvider, WatchingTransformedProvider
{
    /**
     * @param array $transformed
     * @param array<string> $directoriesToWatch
     */
    public function __construct(
        protected array $transformed = [],
        protected array $directoriesToWatch = [],
    ) {
    }

    public function provide(TypeScriptTransformerConfig $config): array
    {
        return $this->transformed;
    }

    public function directoriesToWatch(): array
    {
        return $this->directoriesToWatch;
    }

    public function handleWatchEvent(WatchEvent $watchEvent, TransformedCollection $transformedCollection): int|WatchEventResult
    {
        return 0;
    }
}
