<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Dto;

use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Dto\SimpleDto;

class SimpleDtoCollection extends DataTransferObjectCollection
{
    public function current(): SimpleDto
    {
        return parent::current();
    }
}
