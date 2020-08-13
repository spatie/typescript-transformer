<?php

namespace Spatie\TypescriptTransformer\Tests\Structures;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Exceptions\SymbolAlreadyExists;
use Spatie\TypescriptTransformer\Structures\TypesCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\TypescriptEnum;
use Spatie\TypescriptTransformer\Tests\Fakes\FakeTransformedType;

class TypesCollectionTest extends TestCase
{
    /** @test */
    public function it_can_add_a_null_namespace()
    {
        $structure = TypesCollection::create();

        $structure[] = $fake = FakeTransformedType::fake('Enum')->withoutNamespace();

        $this->assertCount(1, $structure);
        $this->assertEquals([
            'Enum' => $fake,
        ], iterator_to_array($structure));
    }

    /** @test */
    public function it_can_add_types_in_a_multi_layered_namespaces()
    {
        $structure = TypesCollection::create();

        $structure[] = $fakeC = FakeTransformedType::fake('Enum')->withNamespace('a\b\c');
        $structure[] = $fakeB = FakeTransformedType::fake('Enum')->withNamespace('a\b');
        $structure[] = $fakeA = FakeTransformedType::fake('Enum')->withNamespace('a');
        $structure[] = $fake = FakeTransformedType::fake('Enum')->withoutNamespace();

        $this->assertCount(4, $structure);
        $this->assertEquals([
            'Enum' => $fake,
            'a\Enum' => $fakeA,
            'a\b\Enum' => $fakeB,
            'a\b\c\Enum' => $fakeC,
        ], iterator_to_array($structure));
    }

    /** @test */
    public function it_can_add_multiple_types_to_one_namespace()
    {
        $structure = TypesCollection::create();

        $structure[] = $fakeA = FakeTransformedType::fake('EnumA')->withNamespace('test');
        $structure[] = $fakeB = FakeTransformedType::fake('EnumB')->withNamespace('test');

        $this->assertCount(2, $structure);
        $this->assertEquals([
            'test\EnumA' => $fakeA,
            'test\EnumB' => $fakeB,
        ], iterator_to_array($structure));
    }

    /** @test */
    public function it_can_add_a_real_type()
    {
        $reflection = new ReflectionClass(TypescriptEnum::class);

        $structure = TypesCollection::create();

        $structure[] = $fake = FakeTransformedType::fake('TypeScriptEnum')->withReflection($reflection);

        $this->assertCount(1, $structure);
        $this->assertEquals([
            TypescriptEnum::class => $fake,
        ], iterator_to_array($structure));
    }

    /** @test */
    public function it_cannot_have_a_namespace_and_type_with_the_same_name()
    {
        $this->expectException(SymbolAlreadyExists::class);

        $collection = TypesCollection::create();

        $collection[] = $fakeA = FakeTransformedType::fake('Enum')->withNamespace('Enum');
        $collection[] = $fakeB = FakeTransformedType::fake('Enum')->withoutNamespace();
    }

    /** @test */
    public function it_cannot_have_a_namespace_and_type_with_the_same_name_reversed()
    {
        $this->expectException(SymbolAlreadyExists::class);

        $collection = TypesCollection::create();

        $collection[] = $fakeB = FakeTransformedType::fake('Enum')->withoutNamespace();
        $collection[] = $fakeA = FakeTransformedType::fake('Enum')->withNamespace('Enum');
    }

    /** @test */
    public function it_can_get_a_type()
    {
        $collection = TypesCollection::create();

        $collection[] = $fake = FakeTransformedType::fake('Enum')->withNamespace('a\b\c');

        $this->assertEquals($fake, $collection['a\b\c\Enum']);
    }

    /** @test */
    public function it_can_get_a_type_in_the_root_namespace()
    {
        $collection = TypesCollection::create();

        $collection[] = $fake = FakeTransformedType::fake('Enum')->withoutNamespace();

        $this->assertEquals($fake, $collection['Enum']);
    }

    /** @test */
    public function when_searching_a_non_existing_type_null_is_returned()
    {
        $collection = TypesCollection::create();

        $this->assertNull($collection['Enum']);
        $this->assertNull($collection['a\b\Enum']);
        $this->assertNull($collection['a\b\Enum']);
    }

    /** @test */
    public function it_can_add_inline_types_without_structure_checking()
    {
        $collection = TypesCollection::create();

        $collection[] = $fakeA = FakeTransformedType::fake('Enum')->withoutNamespace()->isInline();
        $collection[] = $fakeB = FakeTransformedType::fake('Enum')->withNamespace('Enum');

        $this->assertEquals($fakeA, $collection['Enum']);
        $this->assertEquals($fakeB, $collection['Enum\Enum']);
    }
}
