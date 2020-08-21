---
title: Running
weight: 2
---

When you've configured Typescript transformer in the `typescript-transformer.php` config file you only have to run one command:

```bash
php artisan typescript:transform
```

That's it, your types are now transformed!

There are some extra commands you can use when running the command. It is also possible to only transform one class:
                                                                    
```bash
php artisan typescript:transform --class=app/Enums/RoleEnum.php
```

Or you can define another output file than the default one:

```bash
php artisan typescript:transform --output=types.d.ts
```

This file will be stored in the resource's path of your Laravel application.
