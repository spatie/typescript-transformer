<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Dto;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class StringCollection extends DataTransferObjectCollection
{
    public function current(): string
    {
        return parent::current();
    }
}
