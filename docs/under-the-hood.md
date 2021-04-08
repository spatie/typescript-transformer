---
title: Under the hood
weight: 5
---

Reading this page is not required for knowing how to use the package. We recommend  to first read through the other documentation and then come back to read this page.

## Step 0: configuring the package

In the package configuration a couple important values are defined: 

- the path where your PHP classes are stored,
- the file where the TypeScript definition will be written, 
- the collectors that will find relevant PHP classes that can be transformed,
- and the transformers required to convert PHP to TypeScript.

## Step 1: Collecting classes

We start by iterating over the PHP classes in the specified directory and create a `ReflectionClass` for each class. If a collector can collect one of these classes, it will try to find a suitable transformer.

For example, the `DefaultCollector` will collect each class with a `@typescript` annotation or a `#[TypeScript]` attribute and feed it to all the registered transformers to hopefully find a transformer that can generate a type definition for the class.

## Step 2: Transforming classes

We've created a set of classes and their suitable transformers in step 1. We're now going to transform these types to TypeScript. For the enum example, this is relatively simple, but for a complex data transfer object (DTO) this process is a bit more complicated.

Each property of the DTO will be checked: does it have a PHP type and/or does it have an annotated type? The package creates a unified `Type` from this and feeds it to the type processors. These will transform the type or completely remove it from the DTO's TypeScript definition.

A good example of a type processor is the `ReplaceDefaultsTypeProcessor`. This one will replace some default types you can define in the configuration with a TypeScript representation. For example transforming `DateTime` or `Carbon` types to `string`.

DTO's often have properties that contain other DTO's, or even other custom types. This is why we'll also keep track of the missing symbols when transforming a DTO.
Let's say your DTO has a property that contains another DTO. At the moment of transformation, the package will not know how that other DTO should be transformed. We'll temporarily use a missing symbol that can be replaced by a reference to the correcty DTO type later.

## Step 3: Replacing missing symbols

The classes we started with in step 2 are now transformed into TypeScript definitions, although some types are still missing references to other types. Thanks to the missing symbols collections that each transformer constructed, we can replace these references with the correct type.

If a reference cannot be replaced because it cannot be found the package will default to the `any` type, as it doesn't know how to reference it.

It's recommended to try to avoid these `any` types as much as possible.

## Step 4: persisting types

Our set of transformed classes is now ready. All missing symbols are replaced, so it's time to write them out. A writer will take the entire set of transformed types and write them down into a TypeScript type defintion file you configured.

## Step 5: formatting the output

The package tries to output readable TypeScript code without adhering to any code style. Using tools like Prettier the output can be formatted in a code style of your choice.
