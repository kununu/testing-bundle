<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        $this->setDescription('Load Database Fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $connectionConfig = $this->getContainer()->getParameter(sprintf('kununu_testing.connections.%s', $this->connectionName));

        if (empty($connectionConfig['load_command_fixtures_classes_namespace'])) {
            $output->writeln(sprintf('No fixtures classes are defined for connection "%s"', $this->connectionName));

            return;
        }

        /** @var Orchestrator $orchestrator */
        $orchestrator = $this->getContainer()->get(sprintf('kununu_testing.orchestrator.connections.%s', $this->connectionName));

        $orchestrator->execute($connectionConfig['load_command_fixtures_classes_namespace'], false);

        $output->writeln(sprintf('Fixtures loaded with success for connection "%s"', $this->connectionName));
    }
}
