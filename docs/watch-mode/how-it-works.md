---
title: How does it work?
weight: 1
---

TypeScript transformer can run in a watch mode where it will watch for changes in your PHP files and automatically
regenerate the TypeScript files when a change is detected.

> While this is possibly one of the coolest features of the package it is still heavily experimental and might not work
> in all environments and not always as expected. Feel free to open issues when you encounter problems with a demo
> project and
> an exact plan of steps to reproduce the issue.

In order to be able to watch for changes, the package uses chokidar, a package that is able to watch for file changes in
a
cross-platform way. You'll need to install it as such:

```bash
npm install chokidar
```

Or using yarn:

```bash
yarn add chokidar
```

When running the package in watch mode, it will start a master process which will start a worker process, the idea here
is that the master process always keeps running while the worker process can be restarted when a change is detected
which requires a full application reload.

In order to not always having to reload the full application (and thus starting a new worker) on every file change, the
worker process will smartly swap the Reflection instances used throughout TypeScript transformer when a file change is
detected.

This is the reason why we don't provide Reflection* instances throughout the package, but rather wrapper classes around
these instances. The initial data is loaded by PHP's Reflection API, but afterwards the `roave/better-reflection`
package is used to create in-memory representations of the changed classes.

To make it easy to swap these different types of Reflection instance the package provides Php*Node wrapper classes like:

- PhpClassNode
- PhpPropertyNode
- PhpMethodNode
- PhpParameterNode
- And more...

When your provider however needs something else than the reflection instances provided by these wrapper classes, for
example, checking the container for an instance or the router for the current application routes, you'll need to restart
the worker process in order to get the updated state of your application. We'll come back to this later.
