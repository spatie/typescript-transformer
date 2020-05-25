<?php

namespace Spatie\TypescriptTransformer\Tests\Actions;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypescriptTransformer\Actions\PersistTypesCollectionAction;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\Type;
use Spatie\TypescriptTransformer\TypesCollection;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class PersistTypesCollectionActionTest extends TestCase
{
    use MatchesSnapshots;

    private PersistTypesCollectionAction $action;

    private TemporaryDirectory $temporaryDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = (new TemporaryDirectory())->create();

        $this->action = new PersistTypesCollectionAction(
            TypeScriptTransformerConfig::create()
                ->searchingPath(__DIR__ . '/../FakeClasses')
                ->transformers([MyclabsEnumTransformer::class])
                ->defaultFile('types.d.ts')
                ->outputPath($this->temporaryDirectory->path())
        );
    }

    /** @test */
    public function it_will_persist_the_types()
    {
        $typeCollection = new TypesCollection();

        $typeCollection->add(new Type(
            new ReflectionClass(new class {
            }),
            'types.d.ts',
            'Enum',
            "export type Enum = 'view' | 'edit';"
        ));

        $this->action->execute($typeCollection);

        $this->assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
    }

    /** @test */
    public function it_will_persist_multiple_types_in_one_file()
    {
        $typeCollection = new TypesCollection();

        $typeCollection->add(new Type(
            new ReflectionClass(new class {
            }),
            'types.d.ts',
            'EnumOne',
            "export type EnumOne = 'view' | 'edit';"
        ));

        $typeCollection->add(new Type(
            new ReflectionClass(new class {
            }),
            'types.d.ts',
            'EnumTwo',
            "export type EnumTwo = 'view' | 'edit';"
        ));

        $this->action->execute($typeCollection);

        $this->assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
    }

    /** @test */
    public function it_can_persist_types_in_multiple_directories()
    {
        $typeCollection = new TypesCollection();

        $typeCollection->add(new Type(
            new ReflectionClass(new class {
            }),
            'types.d.ts',
            'Enum',
            "export type Enum = 'view' | 'edit';"
        ));

        $typeCollection->add(new Type(
            new ReflectionClass(new class {
            }),
            'other/types.d.ts',
            'Enum',
            "export type Enum = 'view' | 'edit';"
        ));

        $this->action->execute($typeCollection);

        $this->assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
        $this->assertMatchesFileSnapshot($this->temporaryDirectory->path("other/types.d.ts"));
    }

    /** @test */
    public function it_can_overwrite_files()
    {
        file_put_contents($this->temporaryDirectory->path('types.d.ts'), 'hello');

        $typeCollection = new TypesCollection();

        $typeCollection->add(new Type(
            new ReflectionClass(new class {
            }),
            'types.d.ts',
            'Enum',
            "export type Enum = 'view' | 'edit';"
        ));

        $this->action->execute($typeCollection);

        $this->assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
    }
}
