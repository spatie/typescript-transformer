<?php

namespace Spatie\TypeScriptTransformer\TypeResolvers;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\TypeResolvers\Data\ParsedClass;
use Spatie\TypeScriptTransformer\TypeResolvers\Data\ParsedMethod;
use Spatie\TypeScriptTransformer\TypeResolvers\Data\ParsedNameAndType;

class DocTypeResolver
{
    protected Lexer $lexer;

    protected TypeParser $typeParser;

    protected PhpDocParser $docParser;

    public function __construct()
    {
        $constExprParser = new ConstExprParser();
        $this->typeParser = new TypeParser($constExprParser);

        $this->docParser = new PhpDocParser($this->typeParser, $constExprParser);
        $this->lexer = new Lexer();
    }

    public function class(ReflectionClass $class): ?ParsedClass
    {
        $parsed = $this->parseDocComment($class);

        if ($parsed === null) {
            return null;
        }

        $properties = [];

        foreach ($parsed->getPropertyTagValues() as $propertyTag) {
            $name = ltrim($propertyTag->propertyName, '$');

            $properties[$name] = new ParsedNameAndType($name, $propertyTag->type);
        }

        if (empty($properties)) {
            return null;
        }

        return new ParsedClass($properties);
    }

    public function method(ReflectionMethod $method): ?ParsedMethod
    {
        $parsed = $this->parseDocComment($method);

        if ($parsed === null) {
            return null;
        }

        $parameters = [];

        foreach ($parsed->getParamTagValues() as $paramTag) {
            $name = ltrim($paramTag->parameterName, '$');

            $parameters[$name] = new ParsedNameAndType($name, $paramTag->type);
        }

        $return = null;

        foreach ($parsed->getReturnTagValues() as $returnTag) {
            $return = $returnTag->type;
        }

        if (empty($parameters) && $return === null) {
            return null;
        }

        return new ParsedMethod($parameters, $return);
    }

    public function property(ReflectionProperty $property): ?ParsedNameAndType
    {
        $parsed = $this->parseDocComment($property);

        if ($parsed === null) {
            return null;
        }

        $var = null;

        foreach ($parsed->getVarTagValues() as $varTag) {
            $var = $varTag->type;
        }

        if ($var === null) {
            return null;
        }

        return new ParsedNameAndType($property->name, $var);
    }

    public function type(string $type): TypeNode
    {
        return $this->typeParser->parse(
            new TokenIterator($this->lexer->tokenize($type))
        );
    }

    protected function parseDocComment(
        ReflectionClass|ReflectionMethod|ReflectionProperty $reflection
    ): ?PhpDocNode {
        if ($reflection->getDocComment() === false) {
            return null;
        }

        return $this->docParser->parse(
            new TokenIterator($this->lexer->tokenize($reflection->getDocComment()))
        );
    }
}
