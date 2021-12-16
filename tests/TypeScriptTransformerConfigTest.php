<?php

namespace Spatie\TypeScriptTransformer\Tests;

use DateTime;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Exceptions\InvalidDefaultTypeReplacer;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TypeScriptTransformerConfigTest extends TestCase
{
    /** @test */
    public function it_can_create_transformers()
    {
        $config = TypeScriptTransformerConfig::create()->transformers([
            MyclabsEnumTransformer::class,
        ]);

        $this->assertEquals([new MyclabsEnumTransformer($config)], $config->getTransformers());
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
        $this->expectException(InvalidDefaultTypeReplacer::class);

        $config = TypeScriptTransformerConfig::create()->defaultTypeReplacements([
            'fake-class' => 'string',
        ]);

        $config->getDefaultTypeReplacements();
    }

    /** @test */
    public function it_can_use_a_php_type_in_a_class_property_replacer()
    {
        $config = TypeScriptTransformerConfig::create()->defaultTypeReplacements([
            DateTime::class => 'array<string, string>',
        ]);

        $this->assertEquals(
            [DateTime::class => new Array_(new String_(), new String_())],
            $config->getDefaultTypeReplacements()
        );
    }

    /** @test */
    public function it_can_use_a_typescript_type_in_a_class_property_replacer()
    {
        $config = TypeScriptTransformerConfig::create()->defaultTypeReplacements([
            Dto::class => new TypeScriptType('any'),
        ]);

        $this->assertEquals(
            [Dto::class => new TypeScriptType('any')],
            $config->getDefaultTypeReplacements()
        );
    }

    /** @test */
    public function it_can_use_a_php_dodumenter_type_in_a_class_property_replacer()
    {
        $config = TypeScriptTransformerConfig::create()->defaultTypeReplacements([
            Dto::class => new String_(),
        ]);

        $this->assertEquals(
            [Dto::class => new String_()],
            $config->getDefaultTypeReplacements()
        );
    }
}
