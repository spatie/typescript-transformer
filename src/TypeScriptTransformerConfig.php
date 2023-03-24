<?php

namespace Spatie\TypeScriptTransformer;

use Illuminate\Support\Collection;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use Spatie\TypeScriptTransformer\Exceptions\InvalidDefaultTypeReplacer;
use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\FileSplitters\SingleFileSplitter;
use Spatie\TypeScriptTransformer\FileSplitters\FileSplitter;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\Writers\TypeDefinitionWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;

class TypeScriptTransformerConfig
{
    private array $autoDiscoverTypesPaths = [];

    private array $transformers = [];

    private array $defaultTypeReplacements = [];

    private string $writer = TypeDefinitionWriter::class;

    private ?string $formatter = null;

    private bool $transformToNativeEnums = false;

    private string $outputPath;

    private string $splitter = SingleFileSplitter::class;

    private array $splitterConfig = [
        'filename' => 'types.d.ts',
    ];

    public static function create(): self
    {
        return new self();
    }

    public function autoDiscoverTypes(string ...$paths): self
    {
        $this->autoDiscoverTypesPaths = array_merge($this->autoDiscoverTypesPaths, $paths);

        return $this;
    }

    public function transformer(string $class, array $options = [], ?bool $auto = null): self
    {
        if ($auto !== null) {
            $options['auto'] = $auto;
        }

        $this->transformers[$class] = $options;

        return $this;
    }

    public function transformers(array $transformers): self
    {
        $this->transformers = array_merge(
            $this->transformers,
            $transformers
        );

        return $this;
    }

    public function writer(string $writer): self
    {
        $this->writer = $writer;

        return $this;
    }

    public function outputPath(string $outputPath): self
    {
        $this->outputPath = $outputPath;

        return $this;
    }

    public function splitter(string $splitter, array $config): self
    {
        $this->splitter = $splitter;
        $this->splitterConfig = $config;

        return $this;
    }

    public function defaultTypeReplacements(array $defaultTypeReplacements): self
    {
        $this->defaultTypeReplacements = $defaultTypeReplacements;

        return $this;
    }

    public function formatter(?string $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function getAutoDiscoverTypesPaths(): array
    {
        return $this->autoDiscoverTypesPaths;
    }

    /**@return \Spatie\TypeScriptTransformer\Transformers\Transformer[] */
    public function getTransformers(bool $onlyAuto = false): array
    {
        return collect($this->transformers)
            ->when(
                $onlyAuto,
                fn(Collection $transformers) => $transformers->filter(
                    fn(array $options) => array_key_exists('auto', $options) && $options['auto'] === true
                )
            )
            ->map(fn(array $options, string $class) => $this->buildTransformer($class))
            ->values()
            ->all();
    }

    public function getTransformerOptions(string $class): array
    {
        return $this->transformers[$class];
    }

    public function buildTransformer(string $transformer): Transformer
    {
        return method_exists($transformer, '__construct')
            ? new $transformer($this)
            : new $transformer;
    }

    public function getWriter(): Writer
    {
        return new $this->writer;
    }

    public function getOutputPath(): string
    {
        return rtrim($this->outputPath, '/');
    }

    public function getDefaultTypeReplacements(): array
    {
        $typeResolver = new TypeResolver();

        $replacements = [];

        foreach ($this->defaultTypeReplacements as $class => $replacement) {
            if (! class_exists($class) && ! interface_exists($class)) {
                throw InvalidDefaultTypeReplacer::classDoesNotExist($class);
            }

            $replacements[$class] = $replacement instanceof Type
                ? $replacement
                : $typeResolver->resolve($replacement);
        }

        return $replacements;
    }

    public function getFormatter(): ?Formatter
    {
        if ($this->formatter === null) {
            return null;
        }

        return new $this->formatter;
    }

    public function getFileSplitter(): FileSplitter
    {
        return new $this->splitter($this->splitterConfig);
    }

    public function shouldTransformToNativeEnums(): bool
    {
        return $this->transformToNativeEnums;
    }
}
