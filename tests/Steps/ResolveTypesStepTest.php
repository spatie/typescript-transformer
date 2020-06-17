<?php

namespace Spatie\TypescriptTransformer\Tests\Steps;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Steps\ResolveTypesStep;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\TypescriptEnum;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\TypescriptEnumWithCustomTransformer;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\TypescriptEnumWithName;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

class ResolveTypesStepTest extends TestCase
{
    private ResolveTypesStep $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new ResolveTypesStep(
            new Finder(),
            TypeScriptTransformerConfig::create()
                ->searchingPath(__DIR__ . '/../FakeClasses/Enum')
                ->transformers([MyclabsEnumTransformer::class])
                ->outputFile('types.d.ts')
        );
    }

    /** @test */
    public function it_will_construct_the_type_collection_correctly()
    {
        $typesCollection = $this->action->execute();

        $this->assertCount(3, $typesCollection->getTypes());
    }

    /** @test */
    public function it_parses_a_typescript_enum_correctly()
    {
        $type = $this->action->execute()->getTypes()[TypescriptEnum::class];

        $this->assertEquals(new ReflectionClass(new TypescriptEnum('js')), $type->reflection);
        $this->assertEquals('TypescriptEnum', $type->name);
        $this->assertEquals("export type TypescriptEnum = 'js';", $type->transformed);
        $this->assertEmpty($type->missingSymbols);
    }

    /** @test */
    public function it_parses_a_typescript_enum_with_name_correctly()
    {
        $type = $this->action->execute()->getTypes()[TypescriptEnumWithName::class];

        $this->assertEquals(new ReflectionClass(new TypescriptEnumWithName('js')), $type->reflection);
        $this->assertEquals('EnumWithName', $type->name);
        $this->assertEquals("export type EnumWithName = 'js';", $type->transformed);
        $this->assertEmpty($type->missingSymbols);
    }

    /** @test */
    public function it_parses_a_typescript_enum_with_custom_transformer_correctly()
    {
        $type = $this->action->execute()->getTypes()[TypescriptEnumWithCustomTransformer::class];

        $this->assertEquals(new ReflectionClass(new TypescriptEnumWithCustomTransformer('js')), $type->reflection);
        $this->assertEquals('TypescriptEnumWithCustomTransformer', $type->name);
        $this->assertEquals("fake", $type->transformed);
        $this->assertEmpty($type->missingSymbols);
    }
}
