---
title: How does it work
weight: 4
---

This page is a more in-depth look on how the package works, it is not a requirement to read it if you want to use the package, but it might give you a clearer picture of how it works. I recommend you to first read through the other documentation and then come back to read this page.

## Step 0 configuring the package

In the package configuration, you define the path where your PHP classes are stored, the file where the Typescript will be written, the transformers required to convert PHP to Typescript, and the collectors that will find PHP classes that can be transformed.

## Step 1 collecting classes

We start with iterating over each PHP class in the directory you specified and create a `ReflectionClass` for it. If a collector can collect such a class, it will try to find a suitable transformer. 

For example, the `AnnotationCollector` will collect each class with a `@typescript` annotation and feed it to all the registered transformers to hopefully find one transformer that can transform the class.

## Step 2 transforming classes

We've created a set with classes and their suitable transformers in step 1. Now we're going to transform these types to Typescript. In case of an enum, this is relatively simple, but for a Dto, this process is a bit more complicated.

Each property of a Dto class will be checked: does it have a PHP type and/or does it have an annotated type? The package creates a unified `Type` from this and feeds it to the class property processors. These will transform the type or completely remove it from the Dto's Typescript representation.

A good example of a class property processor is the `ReplaceDefaultTypesClassPropertyProcessor`. This one will replace some default types you defined in your configuration with a Typescript representation. For example transforming `DateTime` or `Carbon` types to `string`.

Another thing happening while transforming is the building up of the missing symbol's collection. Let's say your Dto has a reference to another Dto. At the moment of transformation, the package will not know how that other Dto will be transformed. So we add a missing symbol that can be replaced later by a reference to the other Dto type.

## Step 3 replacing missing symbols

The classes we started with in step 2 are now transformed into Typescript, although some types are missing references to other types. Thanks to the missing symbols collections that each transformer constructed, we can replace these references.

Suppose a reference cannot be replaced because it cannot be found, for example, a reference to a class that is not transformed to Typescript. The package will then replace this reference with `any` since it doesn't know how to reference it.

It would be best if you tried to avoid these `any` types as much as possible.

## Step 4 persisting types

Our set of transformed classes is now ready. All the missing symbols are replaced, so it's time for the last step. We take the whole set of transformed types and write them down into a file you configured.

And that's it!
