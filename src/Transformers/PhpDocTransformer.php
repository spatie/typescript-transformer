<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Exceptions\UnableToTransformUsingAttribute;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\TypeProcessors\DtoCollectionTypeProcessor;
use Spatie\TypeScriptTransformer\TypeProcessors\ReplaceDefaultsTypeProcessor;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class PhpDocTransformer implements Transformer
{
    use TransformsTypes;

    public $none = null;

    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        if (!$this->canTransform($class)) {
            return null;
        }

        $missingSymbols = new MissingSymbolsCollection();
        $tags = $this->getTags($class);

        $type = join([
            $this->transformProperties($class, $tags, $missingSymbols),
            $this->transformMethods($class, $tags, $missingSymbols),
            $this->transformExtra($class, $tags, $missingSymbols),
        ]);

        return TransformedType::create(
            $class,
            $name,
            "{" . PHP_EOL . $type . "}",
            $missingSymbols
        );
    }

    protected function canTransform(ReflectionClass $class): bool
    {
        $hasPhpDoc = $class->getDocComment();
        $hasProperties = $hasPhpDoc && count($this->getTags($class)) > 0;

        return $hasPhpDoc && $hasProperties;
    }

    protected function getTags(ReflectionClass $class): array
    {
        $docComment = $class->getDocComment();

        $factory = DocBlockFactory::createInstance();
        $docBlock = $factory->create($docComment);

        return $docBlock->getTags();
    }

    protected function transformProperties(ReflectionClass          $class,
                                           array                    $tags,
                                           MissingSymbolsCollection $missingSymbols): string
    {
        $isOptional = !empty($class->getAttributes(Optional::class));

        return array_reduce($tags, function (string $carry, Tag $tag) use ($class, $missingSymbols, $isOptional) {
            /** @var Property|PropertyRead|PropertyWrite $tag */

            if (!in_array($tag->getName(), [
                'property',
                'property-read',
            ])) {
                return $carry;
            }

            $type = null;
            foreach ($this->typeProcessors() as $processor) {
                $type = $processor->process(
                    $tag->getType(),
                    new \ReflectionProperty($this, 'none'),
                    $missingSymbols
                );
            }

            if ($type === null) {
                return null;
            }

            $transformed = $this->typeToTypeScript($type, $missingSymbols, $class);

            return $isOptional
                ? "{$carry}{$this->getNameFromTag($tag)}?: {$transformed};" . PHP_EOL
                : "{$carry}{$this->getNameFromTag($tag)}: {$transformed};" . PHP_EOL;

        }, '');
    }

    protected function transformMethods(
        ReflectionClass          $class,
        array                    $tags,
        MissingSymbolsCollection $missingSymbols
    ): string
    {
        return '';
    }

    protected function transformExtra(
        ReflectionClass          $class,
        array                    $tags,
        MissingSymbolsCollection $missingSymbols
    ): string
    {
        return '';
    }

    protected function typeProcessors(): array
    {
        return [
            new ReplaceDefaultsTypeProcessor(
                $this->config->getDefaultTypeReplacements()
            ),
            new DtoCollectionTypeProcessor(),
        ];
    }

    protected function getNameFromTag(Property|PropertyRead|PropertyWrite $tag)
    {
        if ($tag->getVariableName()) {
            return $tag->getVariableName();
        }
        if (!$tag->getDescription()) {
            throw UnableToTransformUsingAttribute::create($tag);
        }
        return preg_split('/(\s|\n)/', trim($tag->getDescription()))[0];
    }
}
