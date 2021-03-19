<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Actions\ResolveTypesCollectionAction;
use Spatie\TypeScriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Exceptions\NoSearchingPathsDefined;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnumWithCustomTransformer;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnumWithName;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\DtoWithChildren;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\LevelUp\YetAnotherDto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDtoCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTypeScriptCollector;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

class ResolveTypesCollectionActionTest extends TestCase
{
    private ResolveTypesCollectionAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new ResolveTypesCollectionAction(
            new Finder(),
            TypeScriptTransformerConfig::create()
                ->searchingPath(__DIR__ . '/../FakeClasses/Enum')
                ->transformers([MyclabsEnumTransformer::class])
                ->collectors([DefaultCollector::class])
                ->outputFile('types.d.ts')
        );
    }

    /** @test */
    public function it_will_construct_the_type_collection_correctly()
    {
        $typesCollection = $this->action->execute();

        $this->assertCount(3, $typesCollection);
    }

    /** @test */
    public function it_will_check_if_searching_paths_are_defined()
    {
        $this->expectException(NoSearchingPathsDefined::class);

        $action = new ResolveTypesCollectionAction(
            new Finder(),
            TypeScriptTransformerConfig::create()
        );

        $action->execute();
    }

    /** @test */
    public function it_parses_a_typescript_enum_correctly()
    {
        $type = $this->action->execute()[TypeScriptEnum::class];

        $this->assertEquals(new ReflectionClass(new TypeScriptEnum('js')), $type->reflection);
        $this->assertEquals('TypeScriptEnum', $type->name);
        $this->assertEquals("'js'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
    }

    /** @test */
    public function it_parses_a_typescript_enum_with_name_correctly()
    {
        $type = $this->action->execute()[TypeScriptEnumWithName::class];

        $this->assertEquals(new ReflectionClass(new TypeScriptEnumWithName('js')), $type->reflection);
        $this->assertEquals('EnumWithName', $type->name);
        $this->assertEquals("'js'", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
    }

    /** @test */
    public function it_parses_a_typescript_enum_with_custom_transformer_correctly()
    {
        $type = $this->action->execute()[TypeScriptEnumWithCustomTransformer::class];

        $this->assertEquals(new ReflectionClass(new TypeScriptEnumWithCustomTransformer('js')), $type->reflection);
        $this->assertEquals('TypeScriptEnumWithCustomTransformer', $type->name);
        $this->assertEquals("fake", $type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
    }

    /** @test */
    public function it_can_parse_multiple_directories()
    {
        $this->action = new ResolveTypesCollectionAction(
            new Finder(),
            TypeScriptTransformerConfig::create()
                ->searchingPath(
                    __DIR__ . '/../FakeClasses/Enum/',
                    __DIR__ . '/../FakeClasses/Integration/'
                )
                ->transformers([MyclabsEnumTransformer::class, DtoTransformer::class])
                ->collectors([DefaultCollector::class])
                ->outputFile('types.d.ts')
        );

        $types = $this->action->execute();

        $this->assertCount(9, $types);

        $this->assertArrayHasKey(TypeScriptEnum::class, $types);
        $this->assertArrayHasKey(TypeScriptEnumWithCustomTransformer::class, $types);
        $this->assertArrayHasKey(TypeScriptEnumWithName::class, $types);

        $this->assertArrayHasKey(Dto::class, $types);
        $this->assertArrayHasKey(DtoWithChildren::class, $types);
        $this->assertArrayHasKey(Enum::class, $types);
        $this->assertArrayHasKey(OtherDto::class, $types);
        $this->assertArrayHasKey(OtherDtoCollection::class, $types);
        $this->assertArrayHasKey(YetAnotherDto::class, $types);
    }

    /** @test */
    public function it_can_add_an_collector_for_types()
    {
        $this->action = new ResolveTypesCollectionAction(
            new Finder(),
            TypeScriptTransformerConfig::create()
                ->searchingPath(__DIR__ . '/../FakeClasses/Enum')
                ->collectors([FakeTypeScriptCollector::class])
                ->outputFile('types.d.ts')
        );

        $types = $this->action->execute();

        $this->assertCount(4, $types);
        $this->assertArrayHasKey(RegularEnum::class, $types);
        $this->assertArrayHasKey(TypeScriptEnum::class, $types);
        $this->assertArrayHasKey(TypeScriptEnumWithCustomTransformer::class, $types);
        $this->assertArrayHasKey(TypeScriptEnumWithName::class, $types);
    }
}
