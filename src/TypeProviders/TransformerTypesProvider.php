<?php

namespace Spatie\TypeScriptTransformer\TypeProviders;

use ReflectionClass;
use ReflectionException;
use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TransformerTypesProvider implements TypesProvider
{
    /**
     * @param  array<Transformer>  $transformers
     * @param  array<string>  $directories
     */
    public function __construct(
        protected array $transformers,
        protected array $directories,
    ) {
    }

    public function provide(
        TypeScriptTransformerConfig $config,
        TypeScriptTransformerLog $log,
        TransformedCollection $types
    ): void {
        $discoveredClasses = (new DiscoverTypesAction())->execute($this->directories);

        foreach ($discoveredClasses as $discoveredClass) {
            $transformed = $this->transformType($discoveredClass);

            if ($transformed) {
                $types->add($transformed);
            }
        }
    }

    /**
     * @param  class-string  $type
     */
    protected function transformType(string $type): ?Transformed
    {
        try {
            $reflection = new ReflectionClass($type);
        } catch (ReflectionException) {
            // TODO: maybe add some kind of log?

            return null;
        }

        foreach ($this->transformers as $transformer) {
            $transformed = $transformer->transform(
                $reflection,
                $this->createTransformationContext($reflection),
            );

            if ($transformed instanceof Transformed) {
                return $transformed;
            }
        }

        return null;
    }

    protected function createTransformationContext(
        ReflectionClass $reflection
    ): TransformationContext {
        $name = $reflection->getShortName();

        $nameSpaceSegments = explode('\\', $reflection->getNamespaceName());

        return new TransformationContext(
            $name,
            $nameSpaceSegments,
        );
    }
}
