<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class FlatWriter implements Writer
{
    public function __construct(
        public string $filename = 'types.ts',
    ) {
    }

    public function output(
        TransformedCollection $collection,
    ): array {
        $output = '';

        $writingContext = new WritingContext(function (Reference $reference) use ($collection) {
            $transformable = $collection->get($reference);

            if (empty($transformable->location)) {
                return $transformable->getName();
            }

            return $transformable->getName();
        });

        foreach ($collection as $transformed) {
            $output .= $transformed->write($writingContext).PHP_EOL;
        }

        return [new WriteableFile($this->filename, $output)];
    }
}
