<?php

namespace Spatie\TypescriptTransformer\Tests\Support;

use PHPUnit\Framework\TestCase;
use Spatie\TypescriptTransformer\Support\TransformerFactory;
use Spatie\TypescriptTransformer\Transformers\DtoTransformer;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class TransformerFactoryTest extends TestCase
{
    private TransformerFactory $factory;

    private TypeScriptTransformerConfig $config;

    protected function setUp() : void
    {
        parent::setUp();

        $this->config = TypeScriptTransformerConfig::create();

        $this->factory = new TransformerFactory($this->config);
    }

    /** @test */
    public function it_can_create_transformers()
    {
        $this->assertEquals(
            new MyclabsEnumTransformer,
            $this->factory->create(MyclabsEnumTransformer::class)
        );
    }

    /** @test */
    public function it_can_create_transformers_with_constructor()
    {
        $this->assertEquals(
            new DtoTransformer($this->config),
            $this->factory->create(DtoTransformer::class)
        );
    }
}
