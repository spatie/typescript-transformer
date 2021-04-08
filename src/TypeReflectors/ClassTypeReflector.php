<?php

namespace Spatie\TypeScriptTransformer\TypeReflectors;

use phpDocumentor\Reflection\Type;
use ReflectionAttribute;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Attributes\InlineTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformableAttribute;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\ClassReader;

class ClassTypeReflector
{
    private bool $transformable = false;

    private bool $inline = false;

    private ?string $name = null;

    private ?string $transformerClass = null;

    private ?Type $type = null;

    public static function create(ReflectionClass $class): self
    {
        return new self($class);
    }

    public function __construct(private ReflectionClass $class)
    {
        $this->reflect();
    }

    public function isTransformable(): bool
    {
        return $this->transformable;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getTransformerClass(): ?string
    {
        return $this->transformerClass;
    }

    public function isInline(): bool
    {
        return $this->inline;
    }

    public function getReflectionClass(): ReflectionClass
    {
        return $this->class;
    }

    private function reflect(): void
    {
        [
            'transformable' => $this->transformable,
            'name' => $this->name,
            'transformer' => $this->transformerClass,
            'inline' => $this->inline,
        ] = (new ClassReader())->forClass($this->class);

        $attributes = $this->class->getAttributes();

        $this->reflectName($attributes)
            ->reflectInline($attributes)
            ->reflectType($attributes)
            ->reflectTransformer($attributes);
    }

    private function reflectName(array $attributes): self
    {
        $nameAttributes = array_values(array_filter(
            $attributes,
            fn (ReflectionAttribute $attribute) => is_a($attribute->getName(), TypeScript::class, true)
        ));

        if (! empty($nameAttributes)) {
            /** @var \Spatie\TypeScriptTransformer\Attributes\TypeScript $nameAttribute */
            $nameAttribute = $nameAttributes[0]->newInstance();

            $this->transformable = true;
            $this->name = $nameAttribute->name ?? $this->name;
        }

        return $this;
    }

    private function reflectInline(array $attributes): self
    {
        $inlineAttributes = array_values(array_filter(
            $attributes,
            fn (ReflectionAttribute $attribute) => is_a($attribute->getName(), InlineTypeScriptType::class, true)
        ));

        if (! empty($inlineAttributes)) {
            $this->transformable = true;
            $this->inline = true;
        }

        return $this;
    }

    private function reflectType(array $attributes): self
    {
        $transformableAttributes = array_values(array_filter(
            $attributes,
            fn (ReflectionAttribute $attribute) => is_a($attribute->getName(), TypeScriptTransformableAttribute::class, true)
        ));

        if (! empty($transformableAttributes)) {
            /** @var \Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformableAttribute $transformableAttribute */
            $transformableAttribute = $transformableAttributes[0]->newInstance();

            $this->transformable = true;
            $this->type = $transformableAttribute->getType();
        }

        return $this;
    }

    private function reflectTransformer(array $attributes): self
    {
        if ($this->type) {
            return $this;
        }

        $transformerAttributes = array_values(array_filter(
            $attributes,
            fn (ReflectionAttribute $attribute) => is_a($attribute->getName(), TypeScriptTransformer::class, true)
        ));

        if (! empty($transformerAttributes)) {
            /** @var \Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformer $transformerAttribute */
            $transformerAttribute = $transformerAttributes[0]->newInstance();

            $this->transformable = true;
            $this->transformerClass = $transformerAttribute->transformer;
        }

        return $this;
    }
}
