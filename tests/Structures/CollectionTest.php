<?php

namespace Spatie\TypescriptTransformer\Tests\Structures;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\Collection;
use Spatie\TypescriptTransformer\Structures\Type;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\TypescriptEnum;
use Spatie\TypescriptTransformer\Tests\Fakes\FakeType;

class CollectionTest extends TestCase
{
    /** @test */
    public function it_can_add_a_null_namespace()
    {
        $structure = Collection::create()->add(
            $fake = FakeType::create('Enum')->withoutNamespace()
        )->getTypes();

        $this->assertCount(1, $structure);
        $this->assertEquals([
            'Enum' => $fake,
        ], $structure);
    }

    /** @test */
    public function it_can_add_types_in_a_multi_layered_namespaces()
    {
        $structure = Collection::create()->add(
            $fakeC = FakeType::create('Enum')->withNamespace('a\b\c')
        )->add(
            $fakeB = FakeType::create('Enum')->withNamespace('a\b')
        )->add(
            $fakeA = FakeType::create('Enum')->withNamespace('a'),
        )->add(
            $fake = FakeType::create('Enum')->withoutNamespace()
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
        $structure = Collection::create()->add(
            $fakeA = FakeType::create('EnumA')->withNamespace('test')
        )->add(
            $fakeB = FakeType::create('EnumB')->withNamespace('test')
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

        Collection::create()->add(
            FakeType::create('Enum')->withNamespace('test')
        )->add(
            FakeType::create('Enum')->withNamespace('test')
        );
    }

    /** @test */
    public function it_can_add_a_real_type()
    {
        $reflection = new  ReflectionClass(TypescriptEnum::class);

        $structure = Collection::create()->add(
            $fake = FakeType::create('TypeScriptEnum')->withReflection($reflection)
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

        Collection::create()->add(
            $fakeA = FakeType::create('Enum')->withNamespace('Enum')
        )->add(
            $fakeB = FakeType::create('Enum')->withoutNamespace()
        );
    }

    /** @test */
    public function it_cannot_have_a_namespace_and_type_with_the_same_name_reversed()
    {
        $this->expectException(Exception::class);

        Collection::create()->add(
            $fakeB = FakeType::create('Enum')->withoutNamespace()
        )->add(
            $fakeA = FakeType::create('Enum')->withNamespace('Enum')
        );
    }

    /** @test */
    public function it_can_get_a_type()
    {
        $collection = Collection::create()->add(
            $fake = FakeType::create('Enum')->withNamespace('a\b\c')
        );

        $this->assertEquals($fake, $collection->find('a\b\c\Enum'));
    }

    /** @test */
    public function it_can_get_a_type_in_the_root_namespace()
    {
        $collection = Collection::create()->add(
            $fake = FakeType::create('Enum')->withoutNamespace()
        );

        $this->assertEquals($fake, $collection->find('Enum'));
    }

    /** @test */
    public function when_searching_a_non_existing_type_null_is_returned()
    {
        $collection = Collection::create();

        $this->assertNull($collection->find('Enum'));
        $this->assertNull($collection->find('a\Enum'));
        $this->assertNull($collection->find('a\b\Enum'));
    }

    /** @test */
    public function it_can_walk_over_types()
    {
        $collection = Collection::create()->add(
            FakeType::create('Enum')->withNamespace('a')
        )->add(
            FakeType::create('OtherEnum')->withNamespace('a')
        )->add(
            FakeType::create('Enum')->withNamespace('a\b')
        )->add(
            FakeType::create('Enum')->withoutNamespace()
        )->add(
            FakeType::create('OtherEnum')->withoutNamespace()
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
        $collection = Collection::create()->add(
            $fake = FakeType::create('Enum')->withNamespace('a')
        );

        $collection->map(function (Type $type) {
            $type->name = 'other_name';

            return $type;
        });

        $this->assertEquals('other_name', $collection->find('a\Enum')->name);
    }
}
