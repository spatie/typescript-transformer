<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class FlatWriter implements Writer
{
    public function __construct(
        public string $filename,
    ) {
    }

    public function output(
        TransformedCollection $collection,
        ReferenceMap $referenceMap
    ): array {
        $output = '';

        $writingContext = new WritingContext(function (Reference $reference) use ($referenceMap) {
            $transformable = $referenceMap->get($reference);

            if (empty($transformable->location)) {
                return $transformable->getName();
            }

            return $transformable->getName();
        });

        foreach ($collection as $transformed) {
            $output .= $transformed->prepareForWrite()->write($writingContext).PHP_EOL;
        }

        return [new WriteableFile($this->filename, $output)];
    }
}
