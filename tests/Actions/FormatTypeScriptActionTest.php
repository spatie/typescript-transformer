<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\FormatTypeScriptAction;
use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class FormatTypeScriptActionTest extends TestCase
{
    use MatchesSnapshots;

    private TemporaryDirectory $temporaryDirectory;

    private string $outputFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = (new TemporaryDirectory())->create();

        $this->outputFile = $this->temporaryDirectory->path('types.d.ts');
    }

    /** @test */
    public function it_can_format_an_generated_file()
    {
        $formatter = new class implements Formatter {
            public function format(string $file): void
            {
                file_put_contents($file, 'formatted');
            }
        };

        $action = new FormatTypeScriptAction(
            TypeScriptTransformerConfig::create()
                ->formatter($formatter::class)
                ->outputFile($this->outputFile)
        );

        file_put_contents(
            $this->outputFile,
            "export type Enum='yes'|'no';export type OtherDto={name:string}"
        );

        $action->execute();

        $this->assertEquals('formatted', file_get_contents($this->outputFile));
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
