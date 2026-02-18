---
title: Setting up the runner
weight: 2
---

Laravel users can skip the following section as the Laravel package already has built-in support for watching changes.

In the beginning of this documentation we saw how to create a command using a runner to transform TypeScript types. In
order to enable watching changes we'll need to set up the runner a bit differently:

```php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTypeScriptCommand extends Command
{
    protected static $defaultName = 'typescript:transform';

    protected function configure(): void
    {
        $this
            ->setDescription('Transform TypeScript types')
            ->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch for file changes')
            ->addOption('worker', null, InputOption::VALUE_NONE, 'Run as worker process');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = new Runner();

        $config = TypeScriptTransformerConfigFactory::create()->get();

        return $runner->run(
            logger: new SymfonyConsoleLogger($output),
            config: $config,
            mode: match ([$input->getOption('watch'), $input->getOption('worker')]) {
                [false, false] => RunnerMode::Direct,
                [true, false] => RunnerMode::Master,
                [true, true] => RunnerMode::Worker,
                default => throw new \Exception('A worker only needs to be started in watch mode.'),
            },
            workerCommand: fn (bool $watch) => 'bin/console typescript:transform --worker '.($watch ? '--watch ' : ''),
        );
    }
}
```

The command above adds two options: `--watch` and `--worker`. The `--watch` option is used to start the master process
which watches for file changes, the `--worker` option is used internally by the master process to start the worker
process.

Feel free to adjust the `workerCommand` closure to your own command structure so that the master process can start the
worker correctly.

## Refactoring with an IDE or AI Agent

When running multiple changes throughout your codebase in multiple files at once like refactoring a class name in
PHPStorm or letting Claude code make multiple changes, the watcher might not be able to pick up these changes
up to ten seconds after they were made. This is due to the fact that most IDEs and AI Agents make changes in a temporary
file and then move the temporary file to the original file location.

In the end TypeScript transformer will pick up these changes, but it might take a bit longer than expected.
