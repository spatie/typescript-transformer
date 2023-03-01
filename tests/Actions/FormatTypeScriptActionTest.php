<?php

use function PHPUnit\Framework\assertEquals;
use function Spatie\Snapshots\assertMatchesFileSnapshot;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\FormatTypeScriptAction;
use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

beforeEach(function () {
    $this->temporaryDirectory = (new TemporaryDirectory())->create();

    $this->outputPath = $this->temporaryDirectory->path();
});

it('can format an generated file', function () {
    $formatter = new class implements Formatter {
        public function format(string $file): void
        {
            file_put_contents($file, 'formatted');
        }
    };

    $action = new FormatTypeScriptAction(
        TypeScriptTransformerConfig::create()->formatter($formatter::class)
    );

    $outputFile = $this->outputPath . '/types.d.ts';

    file_put_contents(
        $outputFile,
        "export type Enum='yes'|'no';export type OtherDto={name:string}"
    );

    $action->execute($outputFile);

    assertEquals('formatted', file_get_contents($outputFile));
});

it('can disable formatting', function () {
    $action = new FormatTypeScriptAction(
        TypeScriptTransformerConfig::create()
    );

    $outputFile = $this->outputPath . '/types.d.ts';

    file_put_contents(
        $outputFile,
        "export type Enum='yes'|'no';export type OtherDto={name:string}"
    );

    $action->execute($outputFile);

    assertMatchesFileSnapshot($outputFile);
});
