<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Illuminate\Contracts\Pagination\CursorPaginator as CursorPaginatorInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorInterface;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptConditional;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNull;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptOperator;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class LaravelTypesProvider implements TypesProvider
{
    public function provide(TypeScriptTransformerConfig $config, TransformedCollection $types): void
    {
        $types->add(
            $this->lengthAwarePaginator(),
            $this->lengthAwarePaginatorInterface(),
            $this->cursorPaginator(),
            $this->cursorPaginatorInterface(),
        );
    }

    protected function lengthAwarePaginator(): Transformed
    {
        return new Transformed(
            new TypeScriptAlias(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('LengthAwarePaginator'),
                    [new TypeScriptIdentifier('TKey'), new TypeScriptIdentifier('TValue')],
                ),
                new TypeScriptObject([
                    new TypeScriptProperty('data', new TypeScriptConditional(
                        TypeScriptOperator::extends(new TypeScriptIdentifier('TKey'), new TypeScriptString()),
                        new TypeScriptGeneric(
                            new TypeScriptIdentifier('Record'),
                            [new TypeScriptIdentifier('TKey'), new TypeScriptIdentifier('TValue')],
                        ),
                        new TypeScriptArray([new TypeScriptIdentifier('TValue')]),
                    )),
                    new TypeScriptProperty('links', new TypeScriptArray([new TypeScriptObject([
                        new TypeScriptProperty('url', new TypeScriptUnion([
                            new TypeScriptIdentifier('string'),
                            new TypeScriptIdentifier('null'),
                        ])),
                        new TypeScriptProperty('label', new TypeScriptString()),
                        new TypeScriptProperty('active', new TypeScriptBoolean()),
                    ])])),
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
            ),
            new ClassStringReference(LengthAwarePaginator::class),
            ['Illuminate'],
            true,
        );
    }

    protected function lengthAwarePaginatorInterface(): Transformed
    {
        return new Transformed(
            new TypeScriptAlias(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('LengthAwarePaginatorInterface'),
                    [new TypeScriptIdentifier('T')],
                ),
                new TypeScriptGeneric(
                    new TypeReference(new ClassStringReference(LengthAwarePaginator::class)),
                    [new TypeScriptIdentifier('T')],
                ),
            ),
            new ClassStringReference(LengthAwarePaginatorInterface::class),
            ['Illuminate'],
            true,
        );
    }

    protected function cursorPaginator(): Transformed
    {
        return new Transformed(
            new TypeScriptAlias(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('CursorPaginator'),
                    [new TypeScriptIdentifier('TKey'), new TypeScriptIdentifier('TValue')],
                ),
                new TypeScriptObject([
                    new TypeScriptProperty('data', new TypeScriptConditional(
                        TypeScriptOperator::extends(new TypeScriptIdentifier('TKey'), new TypeScriptString()),
                        new TypeScriptGeneric(
                            new TypeScriptIdentifier('Record'),
                            [new TypeScriptIdentifier('TKey'), new TypeScriptIdentifier('TValue')],
                        ),
                        new TypeScriptArray([new TypeScriptIdentifier('TValue')]),
                    )),
                    new TypeScriptProperty('links', new TypeScriptArray([new TypeScriptObject([
                        new TypeScriptProperty('url', new TypeScriptUnion([
                            new TypeScriptIdentifier('string'),
                            new TypeScriptIdentifier('null'),
                        ])),
                        new TypeScriptProperty('label', new TypeScriptString()),
                        new TypeScriptProperty('active', new TypeScriptBoolean()),
                    ])])),
                    new TypeScriptProperty('meta', new TypeScriptObject([
                        new TypeScriptProperty('path', new TypeScriptString()),
                        new TypeScriptProperty('per_page', new TypeScriptNumber()),
                        new TypeScriptProperty('next_cursor', new TypeScriptUnion([
                            new TypeScriptString(),
                            new TypeScriptNull(),
                        ])),
                        new TypeScriptProperty('next_page_url', new TypeScriptUnion([
                            new TypeScriptString(),
                            new TypeScriptNull(),
                        ])),
                        new TypeScriptProperty('prev_cursor', new TypeScriptUnion([
                            new TypeScriptString(),
                            new TypeScriptNull(),
                        ])),
                        new TypeScriptProperty('prev_page_url', new TypeScriptUnion([
                            new TypeScriptString(),
                            new TypeScriptNull(),
                        ])),
                    ])),
                ]),
            ),
            new ClassStringReference(CursorPaginator::class),
            ['Illuminate'],
            true,
        );
    }

    protected function cursorPaginatorInterface(): Transformed
    {
        return new Transformed(
            new TypeScriptAlias(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('CursorPaginatorInterface'),
                    [new TypeScriptIdentifier('T')],
                ),
                new TypeScriptGeneric(
                    new TypeReference(new ClassStringReference(CursorPaginator::class)),
                    [new TypeScriptIdentifier('T')],
                ),
            ),
            new ClassStringReference(CursorPaginatorInterface::class),
            ['Illuminate'],
            true,
        );
    }
}
