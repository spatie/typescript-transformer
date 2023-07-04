<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\Watcher\Watch;

class WatchFileSystemAction
{
    public function __construct(
        public TypeScriptTransformerConfig $config
    ) {
    }

    public function execute(
        TransformedCollection $transformedCollection,
    ) {
        Watch::paths($this->config->directories)
            ->onAnyChange(function (string $type, string $path) {
                echo $type.'|'.$path;
            })
            ->start();
    }
}
