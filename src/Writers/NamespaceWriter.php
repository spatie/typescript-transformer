<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamespace;

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
    ): array {
        $split = $this->splitTransformedPerLocationAction->execute(
            $collection
        );

        $output = '';

        $writingContext = new WritingContext(function (Reference $reference) use ($collection) {
            $transformable = $collection->get($reference);

            if (empty($transformable->location)) {
                return $transformable->getName();
            }

            return implode('.', $transformable->location).'.'.$transformable->getName();
        });

        foreach ($split as $splitConstruct) {
            if (count($splitConstruct->segments) === 0) {
                foreach ($splitConstruct->transformed as $transformable) {
                    $output .= $transformable->write($writingContext) . PHP_EOL;
                }

                continue;
            }

            $namespace = new TypeScriptNamespace(
                $splitConstruct->segments,
                $splitConstruct->transformed
            );

            $output .= $namespace->write($writingContext) . PHP_EOL;
        }

        return [new WriteableFile($this->filename, $output)];
    }
}
