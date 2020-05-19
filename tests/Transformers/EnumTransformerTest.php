<?php

namespace Spatie\TypescriptTransformer\Tests\Transformers;

use Spatie\TypescriptTransformer\Tests\TestCase;
use Spatie\TypescriptTransformer\Transformers\EnumTransformer;

class EnumTransformerTest extends TestCase
{
    /** @test */
    public function it_can_transform_enums()
    {
        new EnumTransformer();
    }
}
