<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Annotations;

use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;

class FakeAnnotationsClass
{
    /** @var \Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto */
    public $fsqnProperty;

    /** @var Dto */
    public $property;

    /** @var Dto[] */
    public $arrayProperty;
}
