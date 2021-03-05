<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class LoadDatabaseFixturesCommand extends Command
{
    private $connectionName;
    private $orchestrator;
    private $fixturesClassNames;

    public function __construct(string $connectionName, Orchestrator $orchestrator, array $fixturesClassNames)
    {
        parent::__construct(sprintf('kununu_testing:load_fixtures:connections:%s', $connectionName));

        $this->connectionName = $connectionName;
        $this->orchestrator = $orchestrator;
        $this->fixturesClassNames = $fixturesClassNames;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Load Database Fixtures')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures instead of deleting all data from the database first.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $appendOption = $input->getOption('append');

        $ui = new SymfonyStyle($input, $output);

        if (!$appendOption &&
            !$ui->confirm(
                sprintf('Careful, database "%s" will be purged. Do you want to continue?', $this->connectionName),
                !$input->isInteractive()
            )
        ) {
            return;
        }

        $this->orchestrator->execute($this->fixturesClassNames, $appendOption);

        $output->writeln(sprintf('Fixtures loaded with success for connection "%s"', $this->connectionName));
    }
}
