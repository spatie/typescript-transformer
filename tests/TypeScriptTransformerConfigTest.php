<?php

namespace Spatie\TypeScriptTransformer\Tests;

use DateTime;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Exceptions\InvalidClassPropertyReplacer;
use Spatie\TypeScriptTransformer\Support\TypeScriptType;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TypeScriptTransformerConfigTest extends TestCase
{
    /** @test */
    public function it_can_create_transformers()
    {
        $config = TypeScriptTransformerConfig::create()->transformers([
            MyclabsEnumTransformer::class,
        ]);

        $this->assertEquals([new MyclabsEnumTransformer], $config->getTransformers());
    }

    /** @test */
    public function it_can_create_transformers_with_constructor()
    {
        $config = TypeScriptTransformerConfig::create()->transformers([
            DtoTransformer::class,
        ]);

        $this->assertEquals([new DtoTransformer($config)], $config->getTransformers());
    }

    /** @test */
    public function it_will_check_if_a_class_property_replacement_class_exists()
    {
        $this->expectException(InvalidClassPropertyReplacer::class);

        $config = TypeScriptTransformerConfig::create()->classPropertyReplacements([
            'fake-class' => 'string',
        ]);

        $config->getClassPropertyReplacements();
    }

    /** @test */
    public function it_can_use_a_php_type_in_a_class_property_replacer()
    {
        $config = TypeScriptTransformerConfig::create()->classPropertyReplacements([
            DateTime::class => 'array<string, string>',
        ]);

        $this->assertEquals(
            [DateTime::class => new Array_(new String_(), new String_())],
            $config->getClassPropertyReplacements()
        );
    }

    /** @test */
    public function it_can_use_a_typescript_type_in_a_class_property_replacer()
    {
        $config = TypeScriptTransformerConfig::create()->classPropertyReplacements([
            Dto::class => new TypeScriptType('any'),
        ]);

        $this->assertEquals(
            [Dto::class => new TypeScriptType('any')],
            $config->getClassPropertyReplacements()
        );
    }

    /** @test */
    public function it_can_use_a_php_dodumenter_type_in_a_class_property_replacer()
    {
        $config = TypeScriptTransformerConfig::create()->classPropertyReplacements([
            Dto::class => new String_(),
        ]);

        $this->assertEquals(
            [Dto::class => new String_()],
            $config->getClassPropertyReplacements()
        );
    }
}
