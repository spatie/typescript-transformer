<?php

namespace Spatie\TypescriptTransformer\Tests\Structures;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\Type;
use Spatie\TypescriptTransformer\Structures\TypesCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\TypescriptEnum;
use Spatie\TypescriptTransformer\Tests\Fakes\FakeType;

class TypesCollectionTest extends TestCase
{
    /** @test */
    public function it_can_add_a_null_namespace()
    {
        $structure = TypesCollection::create()->add(
            $fake = FakeType::fake('Enum')->withoutNamespace()
        )->getTypes();

        $this->assertCount(1, $structure);
        $this->assertEquals([
            'Enum' => $fake,
        ], $structure);
    }

    /** @test */
    public function it_can_add_types_in_a_multi_layered_namespaces()
    {
        $structure = TypesCollection::create()->add(
            $fakeC = FakeType::fake('Enum')->withNamespace('a\b\c')
        )->add(
            $fakeB = FakeType::fake('Enum')->withNamespace('a\b')
        )->add(
            $fakeA = FakeType::fake('Enum')->withNamespace('a'),
        )->add(
            $fake = FakeType::fake('Enum')->withoutNamespace()
        )->getTypes();

        $this->assertCount(4, $structure);
        $this->assertEquals([
            'Enum' => $fake,
            'a\Enum' => $fakeA,
            'a\b\Enum' => $fakeB,
            'a\b\c\Enum' => $fakeC,
        ], $structure);
    }

    /** @test */
    public function it_can_add_multiple_types_to_one_namespace()
    {
        $structure = TypesCollection::create()->add(
            $fakeA = FakeType::fake('EnumA')->withNamespace('test')
        )->add(
            $fakeB = FakeType::fake('EnumB')->withNamespace('test')
        )->getTypes();

        $this->assertCount(2, $structure);
        $this->assertEquals([
            'test\EnumA' => $fakeA,
            'test\EnumB' => $fakeB,
        ], $structure);
    }

    /** @test */
    public function it_cannot_add_the_same_type_to_the_same_namespace()
    {
        $this->expectException(Exception::class);

        TypesCollection::create()->add(
            FakeType::fake('Enum')->withNamespace('test')
        )->add(
            FakeType::fake('Enum')->withNamespace('test')
        );
    }

    /** @test */
    public function it_can_add_a_real_type()
    {
        $reflection = new  ReflectionClass(TypescriptEnum::class);

        $structure = TypesCollection::create()->add(
            $fake = FakeType::fake('TypeScriptEnum')->withReflection($reflection)
        )->getTypes();

        $this->assertCount(1, $structure);
        $this->assertEquals([
            TypescriptEnum::class => $fake,
        ], $structure);
    }

    /** @test */
    public function it_cannot_have_a_namespace_and_type_with_the_same_name()
    {
        $this->expectException(Exception::class);

        TypesCollection::create()->add(
            $fakeA = FakeType::fake('Enum')->withNamespace('Enum')
        )->add(
            $fakeB = FakeType::fake('Enum')->withoutNamespace()
        );
    }

    /** @test */
    public function it_cannot_have_a_namespace_and_type_with_the_same_name_reversed()
    {
        $this->expectException(Exception::class);

        TypesCollection::create()->add(
            $fakeB = FakeType::fake('Enum')->withoutNamespace()
        )->add(
            $fakeA = FakeType::fake('Enum')->withNamespace('Enum')
        );
    }

    /** @test */
    public function it_can_get_a_type()
    {
        $collection = TypesCollection::create()->add(
            $fake = FakeType::fake('Enum')->withNamespace('a\b\c')
        );

        $this->assertEquals($fake, $collection->find('a\b\c\Enum'));
    }

    /** @test */
    public function it_can_get_a_type_in_the_root_namespace()
    {
        $collection = TypesCollection::create()->add(
            $fake = FakeType::fake('Enum')->withoutNamespace()
        );

        $this->assertEquals($fake, $collection->find('Enum'));
    }

    /** @test */
    public function when_searching_a_non_existing_type_null_is_returned()
    {
        $collection = TypesCollection::create();

        $this->assertNull($collection->find('Enum'));
        $this->assertNull($collection->find('a\Enum'));
        $this->assertNull($collection->find('a\b\Enum'));
    }

    /** @test */
    public function it_can_walk_over_types()
    {
        $collection = TypesCollection::create()->add(
            FakeType::fake('Enum')->withNamespace('a')
        )->add(
            FakeType::fake('OtherEnum')->withNamespace('a')
        )->add(
            FakeType::fake('Enum')->withNamespace('a\b')
        )->add(
            FakeType::fake('Enum')->withoutNamespace()
        )->add(
            FakeType::fake('OtherEnum')->withoutNamespace()
        );

        $counter = 0;

        $collection->map(function (Type $type) use (&$counter) {
            $this->assertInstanceOf(Type::class, $type);

            $counter++;
        });

        $this->assertEquals(5, $counter);
    }

    /** @test */
    public function it_can_apply_transformations()
    {
        $collection = TypesCollection::create()->add(
            $fake = FakeType::fake('Enum')->withNamespace('a')
        );

        $collection->map(function (Type $type) {
            $type->name = 'other_name';

            return $type;
        });

        $this->assertEquals('other_name', $collection->find('a\Enum')->name);
    }
}
