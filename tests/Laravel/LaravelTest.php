<?php

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Formatters\EslintFormatter;
use Spatie\TypeScriptTransformer\FileSplitters\SingleFileSplitter;
use Spatie\TypeScriptTransformer\Tests\Laravel\LaravelTestCase;
use Spatie\TypeScriptTransformer\Transformers\SpatieStateTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;

uses(LaravelTestCase::class)->in(__DIR__);

beforeEach(function () {
    $this->temporaryDirectory = (new TemporaryDirectory())->create();
});

it('will register the config correctly', function () {
    config()->set('typescript.auto_discover_types', 'fake-searching-path');
    config()->set('typescript.transformers', [
        SpatieStateTransformer::class => []
    ]);
    config()->set('typescript.output_path', __DIR__);
    config()->set('typescript.default_type_replacements', [
        DateTime::class => 'string'
    ]);
    config()->set('typescript.writer', ModuleWriter::class);
    config()->set('typescript.formatter', EslintFormatter::class);
    config()->set('typescript.file_splitter.class', SingleFileSplitter::class);
    config()->set('typescript.file_splitter.options', ['filename' => 'index.d.ts']);

    $config = resolve(TypeScriptTransformerConfig::class);

    expect($config->getAutoDiscoverTypesPaths())->toEqual(['fake-searching-path']);
    expect($config->getTransformers())->toEqual([new SpatieStateTransformer(
        $config
    )]);
    expect($config->getOutputPath())->toEqual(__DIR__);
    expect($config->getDefaultTypeReplacements())->toEqual([
        DateTime::class => 'string'
    ]);
    expect($config->getWriter())->toBeInstanceOf(ModuleWriter::class);
    expect($config->getFormatter())->toBeInstanceOf(EslintFormatter::class);
    expect($config->getFileSplitter())->toEqual(new SingleFileSplitter(['filename' => 'index.d.ts']));
});

it('can transform to typescript', function () {
    config()->set('typescript.auto_discover_types', __DIR__ . '/FakeClasses');
    config()->set('typescript.output_path', $this->temporaryDirectory->path());

    $this->artisan('typescript:transform')->assertExitCode(0);

    expect($this->temporaryDirectory->path('types.d.ts'))->toMatchFileSnapshot();
});

it('can define the input path', function () {
    config()->set('typescript.searching_paths', __DIR__ . '/../FakeClasses');
    config()->set('typescript.output_path', $this->temporaryDirectory->path());

    $this->artisan('typescript:transform --path='. __DIR__ . '/../FakeClasses')->assertExitCode(0);

    expect($this->temporaryDirectory->path('types.d.ts'))->toMatchFileSnapshot();
});

it('can define a relative input path', function () {
    config()->set('typescript.searching_paths', __DIR__ . '/FakeClasses');
    config()->set('typescript.output_path', $this->temporaryDirectory->path());

    $this->app->useAppPath(__DIR__);
    $this->app->setBasePath($this->temporaryDirectory->path('js'));

    $this->artisan('typescript:transform --path=FakeClasses')->assertExitCode(0);

    expect($this->temporaryDirectory->path('types.d.ts'))->toMatchFileSnapshot();
});

it('can define the relative output path', function () {
    config()->set('typescript.searching_paths', __DIR__ . '/FakeClasses');
    config()->set('typescript.output_path', $this->temporaryDirectory->path());

    $this->app->useAppPath(__DIR__);
    $this->app->setBasePath($this->temporaryDirectory->path());

    $this->artisan("typescript:transform --path=FakeClasses --output={$this->temporaryDirectory->path('other')}")->assertExitCode(0);

    expect($this->temporaryDirectory->path('other/types.d.ts'))->toMatchFileSnapshot();
});
