<?php

namespace Spatie\TypeScriptTransformer\Tests\Types;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypeScriptTransformer\Types\StructType;

class StructTypeTest extends TestCase
{
    /** @test */
    public function it_can_create_the_type_from_array()
    {
        $struct = StructType::fromArray([
            'a_string' => 'string',
            'a_float' => 'float',
            'a_class' => RegularEnum::class,
            'an_array' => 'int[]',
            'an_object' => [
                'a_bool' => 'bool',
                'an_int' => 'int'
            ]
        ]);

        $this->assertInstanceOf(StructType::class, $struct);
        $this->assertEquals([
            'a_string' => new String_(),
            'a_float' => new Float_(),
            'a_class' => new Object_(new Fqsen('\\'.RegularEnum::class)),
            'an_array' => new Array_(new Integer()),
            'an_object' => new StructType([
                'a_bool' => new Boolean(),
                'an_int' => new Integer()
            ])
        ], $struct->getTypes());
    }
}
