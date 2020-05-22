<?php

namespace Spatie\TypescriptTransformer\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Exceptions\TypeAlreadyExists;
use Spatie\TypescriptTransformer\Type;
use Spatie\TypescriptTransformer\TypesCollection;

class TypesCollectionTest extends TestCase
{
    private TypesCollection $collection;

    public function setUp(): void
    {
        parent::setUp();

        $this->collection = new TypesCollection();
    }

    /** @test */
    public function it_can_add_a_type(): void
    {
        $this->collection->add(
            $type = $this->createType('types.d.ts', 'Enum')
        );

        $this->assertEquals([
            'types.d.ts' => [
                'Enum' => $type,
            ],
        ], $this->collection->get());
    }

    /** @test */
    public function it_can_add_multiple_types_to_the_same_file(): void
    {
        $this->collection
            ->add($typeOne = $this->createType('types.d.ts', 'EnumOne'))
            ->add($typeTwo = $this->createType('types.d.ts', 'EnumTwo'));

        $this->assertEquals([
            'types.d.ts' => [
                'EnumOne' => $typeOne,
                'EnumTwo' => $typeTwo,
            ],
        ], $this->collection->get());
    }

    /** @test */
    public function it_can_add_the_same_type_to_different_files(): void
    {
        $this->collection
            ->add($typeOne = $this->createType('typesOne.d.ts', 'Enum'))
            ->add($typeTwo = $this->createType('typesTwo.d.ts', 'Enum'));

        $this->assertEquals([
            'typesOne.d.ts' => [
                'Enum' => $typeOne,
            ],
            'typesTwo.d.ts' => [
                'Enum' => $typeTwo,
            ],
        ], $this->collection->get());
    }

    /** @test */
    public function it_cannot_add_the_same_type_to_the_same_file(): void
    {
        $this->expectException(TypeAlreadyExists::class);

        $this->collection
            ->add($this->createType('types.d.ts', 'Enum'))
            ->add($this->createType('types.d.ts', 'Enum'));
    }

    private function createType(string $file, string $name): Type
    {
        return new Type(
            new ReflectionClass(new class {}),
            $file,
            $name,
            'transformed'
        );
    }
}
