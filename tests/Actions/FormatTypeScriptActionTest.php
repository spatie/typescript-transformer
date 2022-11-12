<?php

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\FormatTypeScriptAction;
use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use function PHPUnit\Framework\assertEquals;
use function Spatie\Snapshots\assertMatchesFileSnapshot;

beforeEach(function () {
    $this->temporaryDirectory = (new TemporaryDirectory())->create();

    $this->outputFile = $this->temporaryDirectory->path('types.d.ts');
});

it('can format an generated file', function () {
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

    assertEquals('formatted', file_get_contents($this->outputFile));
});

it('can disable formatting', function () {
    $action = new FormatTypeScriptAction(
        TypeScriptTransformerConfig::create()->outputFile($this->outputFile)
    );

    file_put_contents(
        $this->outputFile,
        "export type Enum='yes'|'no';export type OtherDto={name:string}"
    );

    $action->execute();

    assertMatchesFileSnapshot($this->outputFile);
});
