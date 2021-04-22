<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class LoadFixturesCommand extends Command
{
    private $alias;
    private $orchestrator;
    private $fixturesClassNames;

    public function __construct(string $alias, Orchestrator $orchestrator, array $fixturesClassNames)
    {
        $this->alias = $alias;

        parent::__construct(sprintf('kununu_testing:load_fixtures:%s:%s', static::getFixtureType(), $alias));

        $this->orchestrator = $orchestrator;
        $this->fixturesClassNames = $fixturesClassNames;
    }

    abstract protected static function getFixtureType(): string;

    final protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription(sprintf('Load "%s" default fixtures for alias "%s"', static::getFixtureType(), $this->alias))
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the fixtures instead of purging the storage');
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fixtureType = static::getFixtureType();

        $appendOption = $input->getOption('append');

        $ui = new SymfonyStyle($input, $output);

        if (!$appendOption &&
            !$ui->confirm(
                sprintf('Careful, Fixture type "%s" named "%s" will be purged. Do you want to continue?', $fixtureType, $this->alias),
                !$input->isInteractive()
            )
        ) {
            return 0;
        }

        $this->orchestrator->execute($this->fixturesClassNames, $appendOption);

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
