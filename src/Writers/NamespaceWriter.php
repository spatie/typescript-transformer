<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Support\WrittenFile;
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

    public function output(TransformedCollection $collection, ReferenceMap $referenceMap): array
    {
        $split = $this->splitTransformedPerLocationAction->execute(
            $collection
        );

        $output = '';

        $writingContext = new WritingContext(function (Reference $reference) use ($referenceMap) {
            $transformable = $referenceMap->get($reference);

            return implode('.', $transformable->location).'.'.$transformable->name;
        });

        foreach ($split as $splitConstruct) {
            if (count($splitConstruct->segments) === 0) {
                foreach ($splitConstruct->transformed as $transformable) {
                    $output .= $transformable->typeScriptNode->write($writingContext);
                }

                continue;
            }

            $namespace = new TypeScriptNamespace(
                $splitConstruct->segments,
                array_map(
                    fn (Transformed $transformable) => $transformable->typeScriptNode,
                    $splitConstruct->transformed,
                ),
            );

            $output .= $namespace->write($writingContext);
        }

        file_put_contents(
            $this->filename,
            $output,
        );

        return [
            new WrittenFile($this->filename),
        ];
    }

    public function replaceReference(
        Transformed $transformable
    ): string {
        return implode('.', $transformable->location).'.'.$transformable->name;
    }
}
