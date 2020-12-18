<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\FormatTypeScriptAction;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class FormatTypeScriptActionTest extends TestCase
{
    use MatchesSnapshots;

    private TemporaryDirectory $temporaryDirectory;

    private string $outputFile;

    protected function setUp() : void
    {
        parent::setUp();

        $this->temporaryDirectory = (new TemporaryDirectory())->create();

        $this->outputFile = $this->temporaryDirectory->path('types.d.ts');
    }

    /** @test */
    public function it_can_format_an_generated_file()
    {
        $action = new FormatTypeScriptAction(
            TypeScriptTransformerConfig::create()
                ->enableFormatting()
                ->outputFile($this->outputFile)
        );

        file_put_contents(
            $this->outputFile,
            "export type Enum='yes'|'no';export type OtherDto={name:string}"
        );

        $action->execute();

        $this->assertMatchesFileSnapshot($this->outputFile);
    }

    /** @test */
    public function it_can_disable_formatting()
    {
        $action = new FormatTypeScriptAction(
            TypeScriptTransformerConfig::create()->outputFile($this->outputFile)
        );

        file_put_contents(
            $this->outputFile,
            "export type Enum='yes'|'no';export type OtherDto={name:string}"
        );

        $action->execute();

        $this->assertMatchesFileSnapshot($this->outputFile);
    }
}
