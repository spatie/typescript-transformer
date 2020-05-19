<?php

namespace Spatie\TypescriptTransformer\Actions;

use Spatie\TypescriptTransformer\Type;
use Spatie\TypescriptTransformer\TypesCollection;
use Spatie\TypescriptTransformer\Writers\Writer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PersistTypesCollectionAction
{
    public function execute(TypesCollection $collection): void
    {
        $writer = $this->resolveWriter();
        $basePath = $this->resolveBasePath();

        foreach ($collection->get() as $file => $types) {
            $path = "{$basePath}{$file}";

            if (! File::exists(pathinfo($path, PATHINFO_DIRNAME))) {
                mkdir(pathinfo($path, PATHINFO_DIRNAME), 0755, true);
            }

            file_put_contents(
                $path,
                join(PHP_EOL, array_map(fn (Type $type) => $writer->persist($type), $types))
            );
        }
    }

    private function resolveWriter(): Writer
    {
        /** @var string $writerClass */
        $writerClass = config('typescript-transformer.default_writer');

        return new $writerClass;
    }

    private function resolveBasePath(): string
    {
        $path = trim(config('typescript-transformer.output_path'));

        return Str::endsWith($path, '/') ? $path : "{$path}/";
    }
}
