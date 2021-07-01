<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CopyConnectionSchemaCommand extends Command
{
    private $schemaCopy;
    private $registry;

    public function __construct(SchemaCopyInterface $schemaCopy, ManagerRegistry $registry)
    {
        parent::__construct('kununu_testing:connections:schema:copy');

        $this->schemaCopy = $schemaCopy;
        $this->registry = $registry;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Copy a schema from one connection to another')
            ->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'The source connection to use for this command')
            ->addOption('to', 't', InputOption::VALUE_REQUIRED, 'The destination connection to use for this command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = $input->getOption('from') ?? '';
        $to = $input->getOption('to') ?? '';

        if (!$this->checkConnection($source = $this->getConnection($from), 'from', $from, $output) ||
            !$this->checkConnection($destination = $this->getConnection($to), 'to', $to, $output)
        ) {
            return 2;
        }

        $confirmation = (new SymfonyStyle($input, $output))
            ->confirm(
                sprintf('WARNING! Connection named "%s" schema and data will be PURGED. Do you want to continue?', $to),
                !$input->isInteractive()
            );

        if ($confirmation) {
            $this->schemaCopy->copy($source, $destination);

            return 0;
        }

        return 1;
    }

    private function checkConnection(?Connection $connection, string $arg, string $connectionName, OutputInterface $output): bool
    {
        $connectionName = trim($connectionName);

        if ('' === $connectionName) {
            $output->writeln('');
            $output->writeln(sprintf('"--%s" argument can not be empty', $arg));
            $output->writeln('');

            return false;
        }

        if (!$connection instanceof Connection) {
            $output->writeln('');
            $output->writeln(sprintf('Connection wanted to "--%s" argument: "%s" was not found!', $arg, $connectionName));
            $output->writeln('');

            return false;
        }

        return true;
    }

    private function getConnection(string $connectionName): ?Connection
    {
        /* @var Connection|null $connection */
        try {
            $connection = $this->registry->getConnection($connectionName);
        } catch (InvalidArgumentException $e) {
            $connection = null;
        }

        return $connection;
    }
}
