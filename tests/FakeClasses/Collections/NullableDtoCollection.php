<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Collections;

use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Dto;

class NullableDtoCollection extends DataTransferObjectCollection
{
    public function current(): ?Dto
    {
        return parent::current();
    }
}
