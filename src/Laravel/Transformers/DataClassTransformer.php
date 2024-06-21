<?php

namespace Spatie\TypeScriptTransformer\Laravel\Transformers;

use ReflectionClass;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\Actions\TranspileReflectionTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\ClassPropertyProcessors\FixArrayLikeStructuresClassPropertyProcessor;
use Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors\AddDataCollectionOfInfoClassPropertyProcessor;
use Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors\RemoveDataLazyTypeClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeResolvers\DocTypeResolver;

class DataClassTransformer extends LaravelClassTransformer
{
    protected DataConfig $dataConfig;

    public function __construct(
        protected array $customLazyTypes = [],
        protected array $customDataCollections = [],
        DocTypeResolver $docTypeResolver = new DocTypeResolver(),
        TranspilePhpStanTypeToTypeScriptNodeAction $transpilePhpStanTypeToTypeScriptTypeAction = new TranspilePhpStanTypeToTypeScriptNodeAction(),
        TranspileReflectionTypeToTypeScriptNodeAction $transpileReflectionTypeToTypeScriptTypeAction = new TranspileReflectionTypeToTypeScriptNodeAction(),
    ) {
        $this->dataConfig = app(DataConfig::class);

        parent::__construct($docTypeResolver, $transpilePhpStanTypeToTypeScriptTypeAction, $transpileReflectionTypeToTypeScriptTypeAction);
    }

    protected function shouldTransform(ReflectionClass $reflection): bool
    {
        return $reflection->implementsInterface(BaseData::class);
    }

    protected function classPropertyProcessors(): array
    {
        $processors = parent::classPropertyProcessors();

        foreach ($processors as $processor) {
            if ($processor instanceof FixArrayLikeStructuresClassPropertyProcessor) {
                $processor->replaceArrayLikeClass(
                    \Spatie\LaravelData\DataCollection::class,
                    ...$this->customDataCollections
                );
            }
        }

        $processors[] = new AddDataCollectionOfInfoClassPropertyProcessor();
        $processors[] = new RemoveDataLazyTypeClassPropertyProcessor($this->customLazyTypes);

        return $processors;
    }
}
