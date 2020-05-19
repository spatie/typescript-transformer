<?php

namespace Spatie\TypescriptTransformer\Tests\Writers;

use ReflectionClass;
use Spatie\TypescriptTransformer\Tests\TestCase;
use Spatie\TypescriptTransformer\Type;
use Spatie\TypescriptTransformer\Writers\OptionsWriter;

class OptionsWriterTest extends TestCase
{
    /** @test */
    public function it_writes_the_options_out(): void
    {
        $writer = new OptionsWriter();

        $output = $writer->persist(new Type(
            new ReflectionClass(new class {
            }),
            'index.d.ts',
            'Enum',
            ['a', 'b', 'c', 'd']
        ));

        $this->assertEquals("export type Enum = 'a' | 'b' | 'c' | 'd';", $output);
    }
}
