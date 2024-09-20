<?php

namespace Spatie\TypeScriptTransformer\Laravel\Transformers;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpTypeNodeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\ClassPropertyProcessors\FixArrayLikeStructuresClassPropertyProcessor;
use Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors\DataClassPropertyProcessor;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;
use Spatie\TypeScriptTransformer\TypeResolvers\DocTypeResolver;

class DataClassTransformer extends ClassTransformer
{
    protected DataConfig $dataConfig;

    public function __construct(
        protected array $customLazyTypes = [],
        protected array $customDataCollections = [],
        DocTypeResolver $docTypeResolver = new DocTypeResolver(),
        TranspilePhpStanTypeToTypeScriptNodeAction $transpilePhpStanTypeToTypeScriptTypeAction = new TranspilePhpStanTypeToTypeScriptNodeAction(),
        TranspilePhpTypeNodeToTypeScriptNodeAction $transpilePhpTypeNodeToTypeScriptTypeAction = new TranspilePhpTypeNodeToTypeScriptNodeAction(),
    ) {
        $this->dataConfig = app(DataConfig::class);

        parent::__construct($docTypeResolver, $transpilePhpStanTypeToTypeScriptTypeAction, $transpilePhpTypeNodeToTypeScriptTypeAction);
    }

    protected function shouldTransform(PhpClassNode $phpClassNode): bool
    {
        return $phpClassNode->implementsInterface(BaseData::class);
    }

    protected function classPropertyProcessors(): array
    {
        return [
            new DataClassPropertyProcessor(
                $this->dataConfig,
                $this->customLazyTypes,
            ),
            new FixArrayLikeStructuresClassPropertyProcessor(
                replaceArrays: true,
                arrayLikeClassesToReplace: [
                    \Illuminate\Support\Collection::class,
                    \Illuminate\Database\Eloquent\Collection::class,
                    \Spatie\LaravelData\DataCollection::class,
                    ...$this->customDataCollections,
                ]
            ),
        ];
    }
}
