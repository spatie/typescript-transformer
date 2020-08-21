---
title: How does it work
weight: 4
---

This is a more in depth look on how the package works, it is not a requirement to read it if you want to use the package, but it might give you a clearer picture on how the package works. I recommend you to first read through the other documentation and then come back to read this page.

## Step 0 configuring the package

In the package configuration, you define the path where your PHP classes are stored, the file where the Typescript will be written, the transformers required to convert PHP to Typescript and the collectors that will find PHP classes that can be transformed.

## Step 1 collecting classes

We start with iterating over each PHP class in the directory you specified and create a `ReflectionClass` for it, if a collector can collect such class it will try to find a suitable transformer. 

For example, the `AnnotationCollector` will collect each class with a `@typescript` annotation and feed it to all the registered transformers so it can hopefully find one transfromer that is able to transform the class.

## Step 2 transforming classes

We've created a set with classes and their suitable transformers in step 1, now we're going to transform these types to Typescript. In case of an enum this is relatively simple, but for a Dto this process is a bit more complicated.

Each propery of a Dto class will be checked: does it have an PHP type and/or does it have an annotated type? The package creates a unified `Type` from this and feeds it to the class property processors. These will transform the type or completly remove it from the Dto's Typescript representation.

A good example of a class property processor is the `ReplaceDefaultTypesClassPropertyProcessor`, this one will replace some default types you defined in your configuration with a Typescript representation. For example transforming `DateTime` or `Carbon` types to `string`.

Another thing happening while transforming is the building up of the missing symbol's collection. Let's say your Dto has a reference to another Dto. At the moment of transformation the package will not know how that other Dto will be transformed. So we add a missing symbol that can be replaced later by a reference to the other Dto type.

## Step 3 replacing missing symbols

The set of classes we started with in step 2 is now transformed to Typescript, althouth some types are missing references to other types. Thanks to the missing symbols collections that each transformer constructed we can replace these references.

If a reference cannot be replaced because it cannot be found, for example: a reference to a class that is not transformed to Typescript. Then the package will replace this reference with `any` since it doesn't know how to reference it.

You should try to avoid these `any` types as much as possible.

## Step 4 persisting types

Our set of transformed classes is now ready, all the missing symbols are replaced so it's time for the last step. We take the whole set of transformed types and write it down into a file you configured.

And that's it!


