<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

/**
 * @template T
 */
class GenericClass
{
    /**
     * @param array<T> $data
     */
    public function __construct(
        public int $page = 1,
        public array $data = [],
    ) {
    }
}
