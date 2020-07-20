<?php

namespace Spatie\TypescriptTransformer\Tests\Steps;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypescriptTransformer\Steps\ResolveTypesStep;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\TypescriptEnum;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\TypescriptEnumWithCustomTransformer;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\TypescriptEnumWithName;
use Spatie\TypescriptTransformer\Tests\Fakes\FakeTypescriptCollector;
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
                ->collectors([AnnotationCollector::class])
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
        $this->assertTrue($type->missingSymbols->isEmpty());
    }

    /** @test */
    public function it_parses_a_typescript_enum_with_name_correctly()
    {
        $type = $this->action->execute()->getTypes()[TypescriptEnumWithName::class];

        $this->assertEquals(new ReflectionClass(new TypescriptEnumWithName('js')), $type->reflection);
        $this->assertEquals('EnumWithName', $type->name);
        $this->assertEquals("export type EnumWithName = 'js';", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
    }

    /** @test */
    public function it_parses_a_typescript_enum_with_custom_transformer_correctly()
    {
        $type = $this->action->execute()->getTypes()[TypescriptEnumWithCustomTransformer::class];

        $this->assertEquals(new ReflectionClass(new TypescriptEnumWithCustomTransformer('js')), $type->reflection);
        $this->assertEquals('TypescriptEnumWithCustomTransformer', $type->name);
        $this->assertEquals("fake", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
    }

    /** @test */
    public function it_can_parse_a_specified_file_only()
    {
        $this->action = new ResolveTypesStep(
            new Finder(),
            TypeScriptTransformerConfig::create()
                ->searchingPath(__DIR__ . '/../FakeClasses/Enum/TypescriptEnum.php')
                ->transformers([MyclabsEnumTransformer::class])
                ->collectors([AnnotationCollector::class])
                ->outputFile('types.d.ts')
        );

        $types = $this->action->execute()->getTypes();

        $this->assertCount(1, $types);
        $this->assertArrayHasKey(TypescriptEnum::class, $types);
    }

    /** @test */
    public function it_can_add_an_collector_for_types()
    {
        $this->action = new ResolveTypesStep(
            new Finder(),
            TypeScriptTransformerConfig::create()
                ->searchingPath(__DIR__ . '/../FakeClasses/Enum')
                ->collectors([FakeTypescriptCollector::class])
                ->outputFile('types.d.ts')
        );

        $types = $this->action->execute()->getTypes();

        $this->assertCount(4, $types);
        $this->assertArrayHasKey(RegularEnum::class, $types);
        $this->assertArrayHasKey(TypescriptEnum::class, $types);
        $this->assertArrayHasKey(TypescriptEnumWithCustomTransformer::class, $types);
        $this->assertArrayHasKey(TypescriptEnumWithName::class, $types);
    }
}
