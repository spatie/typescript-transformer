<?php

namespace Spatie\TypescriptTransformer\Tests\Actions;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Actions\ResolveTypesCollectionAction;
use Spatie\TypescriptTransformer\Tests\FakeClasses\TypescriptEnum;
use Spatie\TypescriptTransformer\Tests\FakeClasses\TypescriptEnumWithCustomTransformer;
use Spatie\TypescriptTransformer\Tests\FakeClasses\TypescriptEnumWithName;
use Spatie\TypescriptTransformer\Tests\FakeClasses\TypescriptEnumWithPath;
use Spatie\TypescriptTransformer\Tests\FakeClasses\TypescriptEnumWithPathAndName;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

class ResolveTypesCollectionActionTest extends TestCase
{
    private ResolveTypesCollectionAction $action;

    protected function setUp() : void
    {
        parent::setUp();

        $this->action = new ResolveTypesCollectionAction(
            new Finder(),
            new TypeScriptTransformerConfig(
                __DIR__. '/../FakeClasses',
                [MyclabsEnumTransformer::class],
                'types.d.ts',
                'fake'
            )
        );
    }

    /** @test */
    public function it_will_construct_the_type_collection_correctly()
    {
        $typesCollection = $this->action->execute()->get();

        $this->assertArrayHasKey('types.d.ts', $typesCollection);
        $this->assertArrayHasKey('other/types.d.ts', $typesCollection);

        $types = $typesCollection['types.d.ts'];
        $otherTypes = $typesCollection['other/types.d.ts'];

        $this->assertCount(3, $types);
        $this->assertCount(2, $otherTypes);

        $this->assertArrayHasKey('TypescriptEnum', $types);
        $this->assertArrayHasKey('EnumWithName', $types);
        $this->assertArrayHasKey('TypescriptEnumWithCustomTransformer', $types);

        $this->assertArrayHasKey('TypescriptEnumWithPath', $otherTypes);
        $this->assertArrayHasKey('EnumWithNameAndPath', $otherTypes);
    }

    /** @test */
    public function it_parses_a_typescript_enum_correctly()
    {
        /** @var \Spatie\TypescriptTransformer\Type $type */
        $type = $this->action->execute()->get()['types.d.ts']['TypescriptEnum'];

        $this->assertEquals(new ReflectionClass(new TypescriptEnum('js')), $type->class);
        $this->assertEquals('TypescriptEnum', $type->name);
        $this->assertEquals('types.d.ts', $type->file, );
        $this->assertEquals("export type TypescriptEnum = 'js';", $type->transformed);
    }

    /** @test */
    public function it_parses_a_typescript_enum_with_name_correctly()
    {
        /** @var \Spatie\TypescriptTransformer\Type $type */
        $type = $this->action->execute()->get()['types.d.ts']['EnumWithName'];

        $this->assertEquals(new ReflectionClass(new TypescriptEnumWithName('js')), $type->class);
        $this->assertEquals('EnumWithName', $type->name);
        $this->assertEquals('types.d.ts', $type->file, );
        $this->assertEquals("export type EnumWithName = 'js';", $type->transformed);
    }

    /** @test */
    public function it_parses_a_typescript_enum_with_custom_transformer_correctly()
    {
        /** @var \Spatie\TypescriptTransformer\Type $type */
        $type = $this->action->execute()->get()['types.d.ts']['TypescriptEnumWithCustomTransformer'];

        $this->assertEquals(new ReflectionClass(new TypescriptEnumWithCustomTransformer('js')), $type->class);
        $this->assertEquals('TypescriptEnumWithCustomTransformer', $type->name);
        $this->assertEquals('types.d.ts', $type->file, );
        $this->assertEquals("fake", $type->transformed);
    }

    /** @test */
    public function it_parses_a_typescript_enum_with_path_correctly()
    {
        /** @var \Spatie\TypescriptTransformer\Type $type */
        $type = $this->action->execute()->get()['other/types.d.ts']['TypescriptEnumWithPath'];

        $this->assertEquals(new ReflectionClass(new TypescriptEnumWithPath('js')), $type->class);
        $this->assertEquals('TypescriptEnumWithPath', $type->name);
        $this->assertEquals('other/types.d.ts', $type->file, );
        $this->assertEquals("export type TypescriptEnumWithPath = 'js';", $type->transformed);
    }

    /** @test */
    public function it_parses_a_typescript_enum_with_path_and_name_correctly()
    {
        /** @var \Spatie\TypescriptTransformer\Type $type */
        $type = $this->action->execute()->get()['other/types.d.ts']['EnumWithNameAndPath'];

        $this->assertEquals(new ReflectionClass(new TypescriptEnumWithPathAndName('js')), $type->class);
        $this->assertEquals('EnumWithNameAndPath', $type->name);
        $this->assertEquals('other/types.d.ts', $type->file, );
        $this->assertEquals("export type EnumWithNameAndPath = 'js';", $type->transformed);
    }
}
