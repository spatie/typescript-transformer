<?php

namespace Spatie\TypeScriptTransformer\Actions;

use JsonException;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Writers\MultipleFilesWriter;

class WriteFilesAction
{
    public function __construct(
        public TypeScriptTransformerConfig $config,
    ) {
    }

    /** @param array<WriteableFile> $writeableFiles */
    public function execute(
        array $writeableFiles
    ): void {
        $oldManifest = $this->fetchManifest();

        foreach ($writeableFiles as $writeableFile) {
            if ($oldManifest !== null
                && array_key_exists($writeableFile->path, $oldManifest)
                && $oldManifest[$writeableFile->path] === $writeableFile->hash
            ) {
                continue;
            }

            $this->writeFile($writeableFile);
        }

        $writer = $this->config->writer;

        if (! $writer instanceof MultipleFilesWriter) {
            return;
        }

        $newManifest = $this->buildManifest($writeableFiles);

        $this->deleteOldFiles($oldManifest, $newManifest);

        $this->storeManifest($newManifest);
    }

    protected function writeFile(WriteableFile $file): void
    {
        $fullPath = $this->config->outputDirectory.DIRECTORY_SEPARATOR.$file->path;

        $directory = dirname($fullPath);

        if (is_dir($directory) === false) {
            mkdir($directory, recursive: true);
        }

        file_put_contents($fullPath, $file->contents);
    }

    /** @return array<string, string>|null */
    protected function fetchManifest(): ?array
    {
        $writer = $this->config->writer;

        if (! $writer instanceof MultipleFilesWriter) {
            return null;
        }

        $manifestPath = $this->getManifestPath();

        if (! file_exists($manifestPath)) {
            return null;
        }

        $manifestContent = file_get_contents($manifestPath);

        if ($manifestContent === false) {
            return null;
        }

        try {
            return json_decode($manifestContent, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }
    }

    /**
     * @param array<WriteableFile> $writeableFiles
     *
     * @return array<string, string>
     */
    protected function buildManifest(
        array $writeableFiles,
    ): array {
        $manifest = [];

        foreach ($writeableFiles as $writeableFile) {
            $manifest[$writeableFile->path] = $writeableFile->hash;
        }

        return $manifest;
    }

    /**
     * @param array<string, string>|null $oldManifest
     * @param array<string, string> $newManifest
     */
    protected function deleteOldFiles(
        ?array $oldManifest,
        array $newManifest,
    ): void {
        if ($oldManifest === null) {
            return;
        }

        $filesToDelete = array_keys(array_diff_key(
            $oldManifest,
            $newManifest,
        ));

        foreach ($filesToDelete as $relativePath) {
            $fullPath = $this->config->outputDirectory.DIRECTORY_SEPARATOR.$relativePath;

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    protected function storeManifest(
        array $manifest,
    ): void {
        file_put_contents(
            $this->getManifestPath(),
            json_encode($manifest)
        );
    }

    protected function getManifestPath(): string
    {
        return $this->config->outputDirectory.DIRECTORY_SEPARATOR.'typescript-transformer-manifest.json';
    }
}
