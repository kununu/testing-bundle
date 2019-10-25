<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class LoadDatabaseFixturesCommand extends ContainerAwareCommand
{
    private $connectionName;

    public function __construct(string $connectionName, string $name = null)
    {
        parent::__construct(sprintf('testing-bundle:connection:%s:load_fixtures', $connectionName));

        $this->connectionName = $connectionName;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Load Database Fixtures')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures instead of deleting all data from the database first.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $connectionConfig = $this->getContainer()->getParameter(sprintf('kununu_testing.connections.%s', $this->connectionName));

        if (empty($connectionConfig['load_command_fixtures_classes_namespace'])) {
            $output->writeln(sprintf('No fixtures classes are defined for connection "%s"', $this->connectionName));

            return;
        }

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

        /** @var Orchestrator $orchestrator */
        $orchestrator = $this->getContainer()->get(sprintf('kununu_testing.orchestrator.connections.%s', $this->connectionName));

        $orchestrator->execute($connectionConfig['load_command_fixtures_classes_namespace'], $appendOption);

        $output->writeln(sprintf('Fixtures loaded with success for connection "%s"', $this->connectionName));
    }
}
