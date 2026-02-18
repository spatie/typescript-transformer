<?php

namespace Spatie\TypeScriptTransformer\Tests\TestSupport;

use Spatie\TemporaryDirectory\TemporaryDirectory;

class FakeFileStructureFactory
{
    protected TemporaryDirectory $temporaryDirectory;

    /** @var array<string, FakeFileReference> */
    protected array $references = [];

    public function __construct()
    {
        $this->temporaryDirectory = TemporaryDirectory::make();
    }

    public function getFakeFileReference(string $path, ?string $contents = null): FakeFileReference
    {
        $fullPath = $this->temporaryDirectory->path($path);

        $directory = dirname($fullPath);

        if (! is_dir($directory)) {
            mkdir($directory, recursive: true);
        }

        if ($contents !== null) {
            file_put_contents($fullPath, $contents);
        } else {
            touch($fullPath);
        }

        $reference = new FakeFileReference($fullPath);

        $this->references[$path] = $reference;

        return $reference;
    }

    public function writeFile(string $path, string $contents): string
    {
        $fullPath = $this->temporaryDirectory->path($path);

        $directory = dirname($fullPath);

        if (! is_dir($directory)) {
            mkdir($directory, recursive: true);
        }

        file_put_contents($fullPath, $contents);

        return $fullPath;
    }

    public function path(string $path = ''): string
    {
        return $this->temporaryDirectory->path($path);
    }
}
