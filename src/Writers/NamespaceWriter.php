<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNamespace;

class NamespaceWriter implements Writer
{
    protected SplitTransformedPerLocationAction $splitTransformedPerLocationAction;

    public function __construct(
        public string $filename,
    ) {
        $this->splitTransformedPerLocationAction = new SplitTransformedPerLocationAction();
    }

    public function output(
        TransformedCollection $collection,
        ReferenceMap $referenceMap
    ): array {
        $split = $this->splitTransformedPerLocationAction->execute(
            $collection
        );

        $output = '';

        $writingContext = new WritingContext(function (Reference $reference) use ($referenceMap) {
            $transformable = $referenceMap->get($reference);

            if (empty($transformable->location)) {
                return $transformable->getName();
            }

            return implode('.', $transformable->location).'.'.$transformable->getName();
        });

        foreach ($split as $splitConstruct) {
            if (count($splitConstruct->segments) === 0) {
                foreach ($splitConstruct->transformed as $transformable) {
                    $output .= $transformable->prepareForWrite()->write($writingContext) . PHP_EOL;
                }

                continue;
            }

            $namespace = new TypeScriptNamespace(
                $splitConstruct->segments,
                array_map(
                    fn (Transformed $transformable) => $transformable->prepareForWrite(),
                    $splitConstruct->transformed,
                ),
            );

            $output .= $namespace->write($writingContext) . PHP_EOL;
        }

        return [new WriteableFile($this->filename, $output)];
    }
}
