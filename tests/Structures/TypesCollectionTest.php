<?php

namespace Spatie\TypescriptTransformer\Tests\Structures;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Exceptions\SymbolAlreadyExists;
use Spatie\TypescriptTransformer\Structures\Type;
use Spatie\TypescriptTransformer\Structures\TypesCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\TypescriptEnum;
use Spatie\TypescriptTransformer\Tests\Fakes\FakeType;

class TypesCollectionTest extends TestCase
{
    /** @test */
    public function it_can_add_a_null_namespace()
    {
        $structure = TypesCollection::create();

        $structure[] = $fake = FakeType::fake('Enum')->withoutNamespace();

        $this->assertCount(1, $structure);
        $this->assertEquals([
            'Enum' => $fake,
        ], $structure->getTypes());
    }

    /** @test */
    public function it_can_add_types_in_a_multi_layered_namespaces()
    {
        $structure = TypesCollection::create();

        $structure[] = $fakeC = FakeType::fake('Enum')->withNamespace('a\b\c');
        $structure[] = $fakeB = FakeType::fake('Enum')->withNamespace('a\b');
        $structure[] = $fakeA = FakeType::fake('Enum')->withNamespace('a');
        $structure[] = $fake = FakeType::fake('Enum')->withoutNamespace();

        $this->assertCount(4, $structure);
        $this->assertEquals([
            'Enum' => $fake,
            'a\Enum' => $fakeA,
            'a\b\Enum' => $fakeB,
            'a\b\c\Enum' => $fakeC,
        ], $structure->getTypes());
    }

    /** @test */
    public function it_can_add_multiple_types_to_one_namespace()
    {
        $structure = TypesCollection::create();

        $structure[] = $fakeA = FakeType::fake('EnumA')->withNamespace('test');
        $structure[] = $fakeB = FakeType::fake('EnumB')->withNamespace('test');

        $this->assertCount(2, $structure);
        $this->assertEquals([
            'test\EnumA' => $fakeA,
            'test\EnumB' => $fakeB,
        ], $structure->getTypes());
    }

    /** @test */
    public function it_cannot_add_the_same_type_to_the_same_namespace()
    {
        $this->expectException(SymbolAlreadyExists::class);

        $collection = TypesCollection::create();

        $collection[] = FakeType::fake('Enum')->withNamespace('test');
        $collection[] = FakeType::fake('Enum')->withNamespace('test');
    }

    /** @test */
    public function it_can_add_a_real_type()
    {
        $reflection = new ReflectionClass(TypescriptEnum::class);

        $structure = TypesCollection::create();

        $structure[] = $fake = FakeType::fake('TypeScriptEnum')->withReflection($reflection);

        $this->assertCount(1, $structure);
        $this->assertEquals([
            TypescriptEnum::class => $fake,
        ], $structure->getTypes());
    }

    /** @test */
    public function it_cannot_have_a_namespace_and_type_with_the_same_name()
    {
        $this->expectException(SymbolAlreadyExists::class);

        $collection = TypesCollection::create();

        $collection[] = $fakeA = FakeType::fake('Enum')->withNamespace('Enum');
        $collection[] = $fakeB = FakeType::fake('Enum')->withoutNamespace();
    }

    /** @test */
    public function it_cannot_have_a_namespace_and_type_with_the_same_name_reversed()
    {
        $this->expectException(SymbolAlreadyExists::class);

        $collection = TypesCollection::create();

        $collection[] = $fakeB = FakeType::fake('Enum')->withoutNamespace();
        $collection[] = $fakeA = FakeType::fake('Enum')->withNamespace('Enum');
    }

    /** @test */
    public function it_can_get_a_type()
    {
        $collection = TypesCollection::create();

        $collection[] = $fake = FakeType::fake('Enum')->withNamespace('a\b\c');

        $this->assertEquals($fake, $collection['a\b\c\Enum']);
    }

    /** @test */
    public function it_can_get_a_type_in_the_root_namespace()
    {
        $collection = TypesCollection::create();

        $collection[] = $fake = FakeType::fake('Enum')->withoutNamespace();

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
}
