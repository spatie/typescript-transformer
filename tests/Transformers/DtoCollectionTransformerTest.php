<?php

namespace Spatie\TypescriptTransformer\Tests\Transformers;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypescriptTransformer\Transformers\DtoCollectionTransformer;

class DtoCollectionTransformerTest extends TestCase
{
    use MatchesSnapshots;

    private DtoCollectionTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transformer = new DtoCollectionTransformer();
    }

    /** @test */
    public function it_can_check_if_a_dto_collection_can_be_transformed()
    {
        $this->assertTrue(
            $this->transformer->canTransform(new ReflectionClass(new class extends DataTransferObjectCollection {
            }))
        );

        $this->assertFalse(
            $this->transformer->canTransform(new ReflectionClass(new class {
            }))
        );
    }

    /** @test */
    public function it_can_transform_a_dto_collection()
    {
        [
            'transformed' => $transformed,
            'missingSymbols' => $missingSymbols,
        ] = $this->transformer->execute(
            new ReflectionClass(new class extends DataTransferObjectCollection {
                public function current(): string
                {
                    return parent::current();
                }
            }),
            'Test'
        );

        $this->assertMatchesTextSnapshot($transformed);
        $this->assertCount(0, $missingSymbols);
    }

    /** @test */
    public function it_can_transform_a_dto_collection_with_nullable_type()
    {
        [
            'transformed' => $transformed,
            'missingSymbols' => $missingSymbols,
        ] = $this->transformer->execute(
            new ReflectionClass(new class extends DataTransferObjectCollection {
                public function current(): ?string
                {
                    return parent::current();
                }
            }),
            'Test'
        );

        $this->assertMatchesTextSnapshot($transformed);
        $this->assertCount(0, $missingSymbols);
    }

    /** @test */
    public function it_can_transform_a_dto_collection_with_missing_type()
    {
        [
            'transformed' => $transformed,
            'missingSymbols' => $missingSymbols,
        ] = $this->transformer->execute(
            new ReflectionClass(new class extends DataTransferObjectCollection {
                public function current(): RegularEnum
                {
                    return parent::current();
                }
            }),
            'Test'
        );

        $this->assertMatchesTextSnapshot($transformed);
        $this->assertCount(1, $missingSymbols);
        $this->assertContains(RegularEnum::class, $missingSymbols);
    }
}
