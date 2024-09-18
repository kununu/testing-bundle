<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

use Kununu\TestingBundle\Service\OrchestratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class LoadFixturesCommand extends Command
{
    protected const string OPTION_APPEND = 'append';

    public function __construct(
        private readonly string $alias,
        private readonly OrchestratorInterface $orchestrator,
        private readonly array $fixturesClassNames,
    ) {
        parent::__construct(sprintf('kununu_testing:load_fixtures:%s:%s', static::getFixtureType(), $alias));
    }

    abstract protected function getFixtureType(): string;

    abstract protected function getAliasWord(): string;

    final protected function configure(): void
    {
        $this
            ->setDescription(sprintf('Load default fixtures for %s "%s"', $this->getAliasWord(), $this->alias))
            ->addOption(
                self::OPTION_APPEND,
                null,
                InputOption::VALUE_NONE,
                'Append the fixtures instead of purging the storage'
            );
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fixtureType = static::getFixtureType();

        if (!($append = (bool) filter_var($input->getOption(self::OPTION_APPEND), FILTER_VALIDATE_BOOLEAN))
            && !(new SymfonyStyle($input, $output))->confirm(
                sprintf(
                    'Careful, Fixture type "%s" named "%s" will be purged. Do you want to continue?',
                    $fixtureType,
                    $this->alias
                ),
                !$input->isInteractive()
            )
        ) {
            return 0;
        }

        $this->orchestrator->execute($this->fixturesClassNames, $append);

        $output->writeln(
            sprintf(
                'Fixtures loaded with success for Fixture type "%s" named "%s"',
                $fixtureType,
                $this->alias
            )
        );

        return 0;
    }
}
