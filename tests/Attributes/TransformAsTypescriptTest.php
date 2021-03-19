<?php

namespace Spatie\TypeScriptTransformer\Tests\Attributes;

use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
use phpDocumentor\Reflection\Type;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypeScriptTransformer\Types\StructType;

class TransformAsTypescriptTest extends TestCase
{
    /** @test */
    public function it_can_create_the_attribute_from_string()
    {
        $attribute = new TypeScriptType('string|int');

        $this->assertInstanceOf(Type::class, $attribute->getType());
        $this->assertEquals('string|int', (string) $attribute->getType());
    }

    /** @test */
    public function it_can_create_the_attribute_from_an_array()
    {
        $attribute = new TypeScriptType([
            'a_string' => 'string',
            'a_float' => 'float',
            'a_class' => RegularEnum::class,
            'an_array' => 'int[]',
            'an_object' => [
                'a_bool' => 'bool',
                'an_int' => 'int'
            ]
        ]);

        $this->assertInstanceOf(StructType::class, $attribute->getType());
    }
}
