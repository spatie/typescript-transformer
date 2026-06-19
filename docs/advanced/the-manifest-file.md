---
title: The manifest file
weight: 5
---

When TypeScript transformer writes your types to disk, it also writes a `typescript-transformer-manifest.json` file in
your output directory. This manifest keeps track of every generated file together with a hash of its contents.

On the next run, the manifest is used to:

- Skip rewriting files whose contents did not change (faster runs, fewer file modifications)
- Delete files that were generated before but are no longer produced (cleaning up renamed or removed types)

In most cases you'll want to keep the manifest around. It makes runs cheaper and keeps your output directory in sync with
your PHP classes.

## Disabling the manifest

Sometimes the manifest gets in the way. A few examples:

- The output directory is a committed git submodule, where the manifest shows up as an unexpected tracked file
- The output directory is version-controlled and you prefer a clean diff
- You don't need manifest-based caching (for example in CI, where every run starts from a clean checkout)

You can opt out of manifest generation with `withoutManifest()`:

```php
$config
    ->outputDirectory(resource_path('frontend/types'))
    ->writer(new GlobalNamespaceWriter('generated.d.ts'))
    ->withoutManifest();
```

When the manifest is disabled, every file is written on every run and no `typescript-transformer-manifest.json` is
created.

Keep in mind that disabling the manifest also disables the cleanup of stale files. Because there is no record of what was
written previously, files for types that you rename or remove are left behind in the output directory. You'll need to
clean those up yourself, for example by emptying the output directory before each run.
