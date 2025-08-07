<?php

namespace Spatie\TypeScriptTransformer\TypeResolvers;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpMethodNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpPropertyNode;
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
        $config = new ParserConfig(usedAttributes: []);

        $constExprParser = new ConstExprParser($config);
        $this->typeParser = new TypeParser($config, $constExprParser);

        $this->docParser = new PhpDocParser($config, $this->typeParser, $constExprParser);
        $this->lexer = new Lexer($config);
    }

    public function class(PhpClassNode $phpClassNode): ?ParsedClass
    {
        $parsed = $this->parseDocComment($phpClassNode);

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

    public function method(PhpMethodNode $phpMethodNode): ?ParsedMethod
    {
        $parsed = $this->parseDocComment($phpMethodNode);

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

    public function property(PhpPropertyNode $phpPropertyNode): ?ParsedNameAndType
    {
        $parsed = $this->parseDocComment($phpPropertyNode);

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

        return new ParsedNameAndType($phpPropertyNode->getName(), $var);
    }

    public function type(string $type): TypeNode
    {
        return $this->typeParser->parse(
            new TokenIterator($this->lexer->tokenize($type))
        );
    }

    protected function parseDocComment(
        PhpClassNode|PhpMethodNode|PhpPropertyNode $phpNode
    ): ?PhpDocNode {
        if ($phpNode->getDocComment() === false || $phpNode->getDocComment() === null) {
            return null;
        }

        return $this->docParser->parse(
            new TokenIterator($this->lexer->tokenize($phpNode->getDocComment()))
        );
    }
}
