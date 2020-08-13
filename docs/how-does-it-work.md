---
title: How does it work
weight: 4
---

First, you have to configure the package. In this configuration, you define the path where your PHP classes are stored, the file where the Typescript will be written, and the transformers required to convert PHP to Typescript.

When running the package, it will look in the path you provided for classes with a `@typescript` annotation, and these classes will be given to a list of transformers who will try to convert the PHP class to Typescript. When all PHP classes are processed, the Typescript is written to a file.
