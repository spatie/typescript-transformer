<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\TypeScriptTransformer\DefaultTypeProviders\DefaultTypesProvider;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptExport;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNull;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;

class LaravelDefaultTypesProvider implements DefaultTypesProvider
{
    public function provide(): array
    {
        /** @todo We should only keep these types if they are referenced otherwise they arent't required to be transformed */
        /** @todo writing types in phpdoc syntax would be a lot easier here */

        return [
            $this->collection(),
            $this->eloquentCollection(),
            $this->lengthAwarePaginator(),
            $this->lengthAwarePaginatorInterface(),
        ];
    }

    protected function collection(): Transformed
    {
        return new Transformed(
            new TypeScriptExport(
                new TypeScriptAlias(
                    new TypeScriptGeneric(
                        new TypeScriptIdentifier('Collection'),
                        [new TypeScriptIdentifier('T')],
                    ),
                    new TypeScriptGeneric(
                        new TypeScriptIdentifier('Array'),
                        [new TypeScriptIdentifier('T')],
                    ),
                )
            ),
            new ClassStringReference(Collection::class),
            'Collection',
            true,
            ['Illuminate', 'Support'],
        );
    }

    protected function eloquentCollection(): Transformed
    {
        return new Transformed(
            new TypeScriptExport(
                new TypeScriptAlias(
                    new TypeScriptGeneric(
                        new TypeScriptIdentifier('Collection'),
                        [new TypeScriptIdentifier('T')],
                    ),
                    new TypeScriptGeneric(
                        new TypeScriptIdentifier('Array'),
                        [new TypeScriptIdentifier('T')],
                    ),
                )
            ),
            new ClassStringReference(EloquentCollection::class),
            'Collection',
            true,
            ['Illuminate', 'Database', 'Eloquent', 'Collection'],
        );
    }

    protected function lengthAwarePaginator(): Transformed
    {
        return new Transformed(
            new TypeScriptExport(
                new TypeScriptAlias(
                    new TypeScriptGeneric(
                        new TypeScriptIdentifier('LengthAwarePaginator'),
                        [new TypeScriptIdentifier('T')],
                    ),
                    new TypeScriptObject([
                        new TypeScriptProperty('data', new TypeScriptGeneric(
                            new TypeScriptIdentifier('Array'),
                            [new TypeScriptIdentifier('T')],
                        ),),
                        new TypeScriptProperty('links', new TypeScriptObject([
                            new TypeScriptProperty('url', new TypeScriptUnion([
                                new TypeScriptIdentifier('string'),
                                new TypeScriptIdentifier('null'),
                            ])),
                            new TypeScriptProperty('label', new TypeScriptString()),
                            new TypeScriptProperty('active', new TypeScriptBoolean()),
                        ])),
                        new TypeScriptProperty('meta', new TypeScriptObject([
                            new TypeScriptProperty('total', new TypeScriptNumber()),
                            new TypeScriptProperty('current_page', new TypeScriptNumber()),
                            new TypeScriptProperty('first_page_url', new TypeScriptString()),
                            new TypeScriptProperty('from', new TypeScriptUnion([
                                new TypeScriptNumber(),
                                new TypeScriptNull(),
                            ])),
                            new TypeScriptProperty('last_page', new TypeScriptNumber()),
                            new TypeScriptProperty('last_page_url', new TypeScriptString()),
                            new TypeScriptProperty('next_page_url', new TypeScriptUnion([
                                new TypeScriptString(),
                                new TypeScriptNull(),
                            ])),
                            new TypeScriptProperty('path', new TypeScriptString()),
                            new TypeScriptProperty('per_page', new TypeScriptNumber()),
                            new TypeScriptProperty('prev_page_url', new TypeScriptUnion([
                                new TypeScriptString(),
                                new TypeScriptNull(),
                            ])),
                            new TypeScriptProperty('to', new TypeScriptUnion([
                                new TypeScriptNumber(),
                                new TypeScriptNull(),
                            ])),
                        ])),
                    ]),
                )
            ),
            new ClassStringReference(LengthAwarePaginator::class),
            'LengthAwarePaginator',
            true,
            ['Illuminate', 'Pagination'],
        );
    }

    protected function lengthAwarePaginatorInterface(): Transformed
    {
        return new Transformed(
            new TypeScriptExport(
                new TypeScriptAlias(
                    new TypeScriptGeneric(
                        new TypeScriptIdentifier('LengthAwarePaginator'),
                        [new TypeScriptIdentifier('T')],
                    ),
                    new TypeScriptGeneric(
                        new TypeReference(new ClassStringReference(LengthAwarePaginator::class)),
                        [new TypeScriptIdentifier('T')],
                    ),
                )
            ),
            new ClassStringReference(LengthAwarePaginatorInterface::class),
            'LengthAwarePaginator',
            true,
            ['Illuminate', 'Contracts', 'Pagination'],
        );
    }
}
