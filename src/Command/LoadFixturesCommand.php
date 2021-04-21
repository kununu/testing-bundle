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
        parent::__construct(static::getNameByAlias($alias));

        $this->alias = $alias;
        $this->orchestrator = $orchestrator;
        $this->fixturesClassNames = $fixturesClassNames;
    }

    final public static function getNameByAlias(string $alias): string
    {
        return sprintf('kununu_testing:load_fixtures:%s:%s', static::getFixtureType(), $alias);
    }

    abstract protected static function getFixtureType(): string;

    final protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription(sprintf('Load "%s" fixtures', static::getFixtureType()))
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the fixtures instead of purging the storage');
    }

    final protected function execute(InputInterface $input, OutputInterface $output): void
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
            return;
        }

        $this->orchestrator->execute($this->fixturesClassNames, $appendOption);

        $output->writeln(
            sprintf(
                'Fixtures loaded with success for Fixture type "%s" named "%s"',
                $fixtureType,
                $this->alias
            )
        );
    }
}
