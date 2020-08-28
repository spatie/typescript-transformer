<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class StringDtoCollection extends DataTransferObjectCollection
{
    public function current(): string
    {
        return parent::current();
    }
}
