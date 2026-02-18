---
title: Setting up
weight: 1
---

We first need to initialize typescript-transformer and configure what it exactly should do. For Laravel users, we have a dedicated [Laravel](/docs/typescript-transformer/v3/laravel/installation-and-setup) section.

Since TypeScript transformer is framework-agnostic, we cannot provide you exact steps on how to integrate it into your
application. However, we can provide you with a general idea of how to do it.

Ideally, TypeScript transformer is a CLI command within your application, that can be quickly called when you need to
generate TypeScript types.

Within Symphony, for example, you can create a command like this:

```php
use Spatie\TypeScriptTransformer\Enums\RunnerMode;
use Spatie\TypeScriptTransformer\Runners\Runner;
use Spatie\TypeScriptTransformer\Support\Console\SymfonyConsoleLogger;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTypeScriptCommand extends Command
{
    protected static $defaultName = 'typescript:transform';

    protected function configure(): void
    {
        $this->setDescription('Transform TypeScript types');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = new Runner();

        $config = TypeScriptTransformerConfigFactory::create()->get(); // We'll come back to this in a minute

        return $runner->run(
            logger: new SymfonyConsoleLogger($output),
            config: $config,
            mode: RunnerMode::Direct,
        );
    }
}
```

We dive further into configuring runners later on.

When you've registered the command, it can be executed as such:

```bash
php bin/console typescript:transform
```

Since we haven't configured TypeScript transformer yet, this command won't do anything. Continue with the next section to learn how to configure TypeScript transformer.
