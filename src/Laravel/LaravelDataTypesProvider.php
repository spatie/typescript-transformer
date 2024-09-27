<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class LaravelDataTypesProvider implements TypesProvider
{
    public function provide(TypeScriptTransformerConfig $config, TransformedCollection $types): void
    {
        $types->add(
            $this->paginatedCollection(),
            $this->cursorPaginatedCollection(),
        );
    }

    protected function paginatedCollection(): Transformed
    {
        return new Transformed(
            new TypeScriptAlias(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('PaginatedDataCollection'),
                    [new TypeScriptIdentifier('TKey'), new TypeScriptIdentifier('TValue')],
                ),
                new TypeReference(new ClassStringReference(LengthAwarePaginator::class))
            ),
            new ClassStringReference(PaginatedDataCollection::class),
            ['Spatie', 'LaravelData'],
            true,
        );
    }

    protected function cursorPaginatedCollection(): Transformed
    {
        return new Transformed(
            new TypeScriptAlias(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('CursorPaginatedDataCollection'),
                    [new TypeScriptIdentifier('TKey'), new TypeScriptIdentifier('TValue')],
                ),
                new TypeReference(new ClassStringReference(CursorPaginator::class))
            ),
            new ClassStringReference(CursorPaginatedDataCollection::class),
            ['Spatie', 'LaravelData'],
            true,
        );
    }
}
