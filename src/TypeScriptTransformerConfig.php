<?php

namespace Spatie\TypeScriptTransformer;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Exceptions\InvalidDefaultTypeReplacer;
use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\Writers\TypeDefinitionWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;

class TypeScriptTransformerConfig
{
    private array $autoDiscoverTypesPaths = [];

    private array $transformers = [];

    private array $collectors = [DefaultCollector::class];

    private string $outputFile = 'types.d.ts';

    private array $defaultTypeReplacements = [];

    private string $writer = TypeDefinitionWriter::class;

    private ?string $formatter = null;

    private bool $transformToNativeEnums = false;

    public static function create(): self
    {
        return new self();
    }

    public function autoDiscoverTypes(string ...$paths): self
    {
        $this->autoDiscoverTypesPaths = array_merge($this->autoDiscoverTypesPaths, $paths);

        return $this;
    }

    public function transformers(array $transformers): self
    {
        $this->transformers = $transformers;

        return $this;
    }

    public function collectors(array $collectors)
    {
        $this->collectors = array_merge($collectors, [DefaultCollector::class]);

        return $this;
    }

    public function writer(string $writer): self
    {
        $this->writer = $writer;

        return $this;
    }

    public function outputFile(string $defaultFile): self
    {
        $this->outputFile = $defaultFile;

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

    public function transformToNativeEnums(bool $transformToNativeEnums = true): self
    {
        $this->transformToNativeEnums = $transformToNativeEnums;

        return $this;
    }

    public function getAutoDiscoverTypesPaths(): array
    {
        return $this->autoDiscoverTypesPaths;
    }

    /**@return \Spatie\TypeScriptTransformer\Transformers\Transformer[] */
    public function getTransformers(): array
    {
        return array_map(
            fn (string $transformer) => $this->buildTransformer($transformer),
            $this->transformers
        );
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

    public function getOutputFile(): string
    {
        return $this->outputFile;
    }

    /** @return \Spatie\TypeScriptTransformer\Collectors\Collector[] */
    public function getCollectors(): array
    {
        return array_map(
            fn (string $collector) => new $collector($this),
            $this->collectors
        );
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

    public function shouldTransformToNativeEnums(): bool
    {
        return $this->transformToNativeEnums;
    }
}
