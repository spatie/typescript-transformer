<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections;

use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;

class NullableDtoCollection extends DataTransferObjectCollection
{
    public function current(): ?Dto
    {
        return parent::current();
    }
}
