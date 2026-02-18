<?php

namespace Spatie\TypeScriptTransformer\Tests\TestSupport;

use Spatie\TypeScriptTransformer\References\FilesystemReference;
use Spatie\TypeScriptTransformer\References\Reference;

class FakeFileReference implements Reference, FilesystemReference
{
    public function __construct(
        public string $path,
    ) {
    }

    public function getKey(): string
    {
        return $this->path;
    }

    public function humanFriendlyName(): string
    {
        return $this->path;
    }

    public function getFilesystemOriginPath(): string
    {
        return $this->path;
    }
}
