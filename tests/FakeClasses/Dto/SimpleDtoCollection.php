<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Dto;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class SimpleDtoCollection extends DataTransferObjectCollection
{
    public function current(): SimpleDto
    {
        return parent::current();
    }
}
