<?php

namespace Spatie\TypeScriptTransformer\Output;

use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class MultipleFileOutput extends Output
{
    public function __construct(protected TypeScriptTransformerConfig $config)
    {
        $path = $config->getOutputDestination();
        $this->path = is_dir($path) ? rtrim($path, '/') : pathinfo($path, PATHINFO_DIRNAME);
        $this->output = [];

    }

    public function append(string $typeScript, string $classPath = null): void
    {
        $pathName = ltrim(str_replace('\\', '/', $classPath), '/');

        $this->output[$pathName] = $typeScript;
    }

    public function writeOut($fileType): array
    {
        $this->clearPrevious($fileType);

        foreach ($this->output as $path => $contents) {
            if ($contents === '') {
                continue;
            }

            $path = "$this->path/$path.{$fileType}";

            $this->ensureFilesExist($path);
            file_put_contents(
                $path,
                $contents
            );
        }

        return array_keys($this->output);
    }

    public function writesMultipleFiles(): bool
    {
        return true;
    }

    protected function clearPrevious(string $fileType): void
    {
        // In case there are previously defined types that are no longer needed
        if (is_dir($this->path)) {
            // Delete the namespaced folder as that's most likely to contain only generated files
            // as apposed to manually defined types not generated from PHP classes
            $baseNamespaces = array_map(
                 fn (string $relativePath) => preg_replace(
                    "/.{$fileType}$/",
                    '',
                    explode('/', $relativePath)[0]
                ),
                array_keys($this->output)
            );
            $uniqueNamespaces = array_keys(
                array_flip($baseNamespaces)
            );

            // Clear out each of the folders
            // PHP can't delete a folder that's not empty
            foreach ($uniqueNamespaces as $namespace) {
                $fullPath = "{$this->path}/{$namespace}";
                if (!is_dir($fullPath)) {
                    continue;
                }

                $directoryIterator = new \RecursiveDirectoryIterator(
                    $fullPath,
                    \FilesystemIterator::SKIP_DOTS
                );
                $files = new \RecursiveIteratorIterator(
                    $directoryIterator,
                    \RecursiveIteratorIterator::CHILD_FIRST
                );

                /** @var \SplFileInfo $file */
                foreach ($files as $file) {
                    $file->isDir() && rmdir($file->getPathname());
                    $file->isFile() && unlink($file->getPathname());
                }

                rmdir($fullPath);
            }
        }

    }

}
