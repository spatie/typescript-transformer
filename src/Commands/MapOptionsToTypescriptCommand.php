<?php

namespace Spatie\TypescriptTransformer\Commands;

use Spatie\TypescriptTransformer\Actions\PersistTypesCollectionAction;
use Spatie\TypescriptTransformer\Actions\ResolveTypesCollectionAction;
use Exception;
use Illuminate\Console\Command;
use Spatie\TypescriptTransformer\Type;

class MapOptionsToTypescriptCommand extends Command
{
    protected $signature = 'options:map-to-typescript';

    protected $description = 'Map enums/states to typescript';

    public function handle(
        ResolveTypesCollectionAction $resolveTypesCollectionAction,
        PersistTypesCollectionAction $persistTypesCollectionAction
    ): void {
        try {
            $typesCollection = $resolveTypesCollectionAction->execute();
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return;
        }

        $persistTypesCollectionAction->execute($typesCollection);

        foreach ($typesCollection->get() as $file => $types) {
            $typesString = join(', ', array_map(fn (Type $type) => $type->name, $types));

            $this->info("Written {$file} with types: {$typesString}");
        }
    }
}
