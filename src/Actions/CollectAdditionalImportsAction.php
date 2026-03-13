<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Attributes\AdditionalImport;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Visitor\Visitor;

class CollectAdditionalImportsAction
{
    protected Visitor $visitor;

    public function __construct(
        protected TypeScriptTransformerConfig $config,
        protected ResolveRelativePathAction $resolveRelativePathAction = new ResolveRelativePathAction(),
    ) {
        $this->visitor = $this->resolveVisitor();
    }

    public function execute(TransformedCollection $collection): void
    {
        foreach ($collection->onlyChanged() as $transformed) {
            $metadata = [
                'transformed' => $transformed,
            ];

            $this->visitor->execute($transformed->getNode(), $metadata);
        }
    }

    protected function resolveVisitor(): Visitor
    {
        return Visitor::create()->before(function (TypeScriptRaw $raw, array &$metadata) {
            if ($raw->additionalImports === []) {
                return;
            }

            /** @var Transformed $transformed */
            $transformed = $metadata['transformed'];

            foreach ($raw->additionalImports as $import) {
                $transformed->addAdditionalImport($this->normalizeImport($import));
            }
        }, [TypeScriptRaw::class]);
    }

    protected function normalizeImport(AdditionalImport $import): AdditionalImport
    {
        if (! $this->isAbsolutePath($import->path)) {
            return $import;
        }

        $importPath = realpath($import->path) ?: $import->path;

        $relativePath = $this->resolveRelativePathAction->execute(
            $this->config->outputDirectory,
            $importPath,
        );

        return new AdditionalImport($relativePath, $import->names);
    }

    protected function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }
}
