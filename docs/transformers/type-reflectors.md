---
title: Type reflectors
weight: 2
---

Writing transformers can be complicated since there's a lot to keep in mind when trying to resolve the types within PHP
classes.

TypeReflectors can help you with this. They will take a `ReflectionMethod`, `ReflectionProperty`
or `ReflectionParameter` and convert it into a `Type` which can be easily transpiled to TypeScript.

A type reflector uses the following information to deduce a type:

- attributes added to the PHP definition
- an annotation was added with the PHP definition
- the type is written in PHP, and if it is nullable

It will use all this information and creates a `Type` object from
the [phpDocumentor/TypeResolver](https://github.com/phpDocumentor/TypeResolver) package, examples of such types are:

- Array_
- Boolean
- Compound
- Object_
- Void
- [and many more](https://github.com/phpDocumentor/TypeResolver/tree/1.x/src/Types)

These types can be easily transpiled to TypeScript. Let's take a look at an example:

```php
class Properties{
    #[LiteralTypeScriptType('unknown')]
    public $propertyWithAttribute;
    
    /** @var int */
    public $propertyWithAnnotation;
    
    public bool $propertyWithType;
    
    public ?string $propertyWithNullableType;
};
```

We can now write a transformer that uses the `TransformsTypes` trait. This trait adds the `reflectionToTypeScript` method to your transformer, which takes a reflected entity and a missing symbols collection and transforms it to Typescript.

```php

class PropertyTransformer implements Transformer{
    use TransformsTypes;

    public function transform(ReflectionClass $class, string $name) : ?TransformedType
    {
        $missingSymbols = new MissingSymbolsCollection();
        
        $properties = array_map(
            fn(ReflectionProperty $reflection) => "{$reflection->name}: {$this->reflectionToTypeScript($reflection, $missingSymbols)};",
            $class->getProperties()
        );
        
        return TransformedType::create(
            $class, 
            $name, 
            '{'. join($properties) . '}', 
            $missingSymbols
        );
    }
}
```

This transformer will transform the `Properties` class into:

```tsx
{
    propertyWithAttribute: unknown;
    propertyWithAnnotation: number;
    propertyWithType: boolean;
    propertyWithNullableType: ?string;
}
```
