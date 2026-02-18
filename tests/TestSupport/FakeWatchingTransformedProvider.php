<?php

namespace Spatie\TypeScriptTransformer\Tests\TestSupport;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\WatchEvent;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\WatchingTransformedProvider;

class FakeWatchingTransformedProvider implements TransformedProvider, WatchingTransformedProvider
{
    /** @var array<WatchEvent> */
    public array $receivedEvents = [];

    /**
     * @param array $transformed
     * @param array<string> $directoriesToWatch
     */
    public function __construct(
        protected array $transformed = [],
        protected array $directoriesToWatch = [],
    ) {
    }

    public function provide(): array
    {
        return $this->transformed;
    }

    public function directoriesToWatch(): array
    {
        return $this->directoriesToWatch;
    }

    public function handleWatchEvent(WatchEvent $watchEvent, TransformedCollection $transformedCollection): ?WatchEventResult
    {
        $this->receivedEvents[] = $watchEvent;

        return null;
    }
}
