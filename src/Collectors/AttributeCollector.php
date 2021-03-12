<?php

namespace Spatie\TypeScriptTransformer\Collectors;

use Exception;
use ReflectionAttribute;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformableAttribute;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\ClassReader;
use Spatie\TypeScriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypeScriptTransformer\Structures\CollectedOccurrence;
use Spatie\TypeScriptTransformer\TransformerFactory;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class AttributeCollector extends Collector
{
    protected ClassReader $classReader;

    protected TransformerFactory $transformerFactory;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        parent::__construct($config);

        $this->classReader = new ClassReader();
        $this->transformerFactory = new TransformerFactory($config);
    }

    public function shouldCollect(ReflectionClass $class): bool
    {
        $attributes = array_merge(
            $class->getAttributes(TypeScript::class, ReflectionAttribute::IS_INSTANCEOF),
            $class->getAttributes(TypeScriptTransformableAttribute::class, ReflectionAttribute::IS_INSTANCEOF),
        );

        return ! empty($attributes);
    }

    public function getCollectedOccurrence(ReflectionClass $class): CollectedOccurrence
    {
        $name = $this->resolveName($class);

        $typeScriptTransformableAttributes = $class->getAttributes(
            TypeScriptTransformableAttribute::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        if (! empty($typeScriptTransformableAttributes)) {
            /** @var \Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformableAttribute $attribute */
            $attribute = current($typeScriptTransformableAttributes);

            throw new Exception('Not yet implemented');
            // TODO: implement a transformer
        }

        return CollectedOccurrence::create(
            $this->resolveTransformer($class),
            $name
        );
    }

    protected function resolveTransformer(
        ReflectionClass $class,
    ): Transformer {
        $transformerAttributes = $class->getAttributes(
            TypeScriptTransformer::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        if (! empty($transformerAttributes)) {
            /** @var \Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformer $transformerClass */
            $transformerAttribute = $transformerAttributes[0];

            return $this->transformerFactory->create($transformerAttribute->newInstance()->transformer);
        }

        foreach ($this->config->getTransformers() as $transformer) {
            if ($transformer->canTransform($class)) {
                return $transformer;
            }
        }

        throw TransformerNotFound::create($class);
    }

    private function resolveName(ReflectionClass $class): string
    {
        $nameAttributes = $class->getAttributes(
            TypeScript::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        if (empty($nameAttribute)) {
            return $class->getShortName();
        }

        /** @var \Spatie\TypeScriptTransformer\Attributes\TypeScript $nameAttribute */
        $nameAttribute = $nameAttributes[0]->newInstance();

        return $nameAttribute->name;
    }
}
