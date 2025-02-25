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
    private const string OPTION_FROM = 'from';
    private const string OPTION_FROM_SHORT = 'f';
    private const string OPTION_TO = 'to';
    private const string OPTION_TO_SHORT = 't';

    public function __construct(
        private readonly SchemaCopyInterface $schemaCopy,
        private readonly ManagerRegistry $registry,
    ) {
        parent::__construct('kununu_testing:connections:schema:copy');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Copy a schema from one connection to another')
            ->addOption(
                self::OPTION_FROM,
                self::OPTION_FROM_SHORT,
                InputOption::VALUE_REQUIRED,
                'The source connection to use for this command'
            )
            ->addOption(
                self::OPTION_TO,
                self::OPTION_TO_SHORT,
                InputOption::VALUE_REQUIRED,
                'The destination connection to use for this command'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = $input->getOption(self::OPTION_FROM) ?? '';
        $to = $input->getOption(self::OPTION_TO) ?? '';

        if (!$this->checkConnection($source = $this->getConnection($from), self::OPTION_FROM, $from, $output)
            || !$this->checkConnection($destination = $this->getConnection($to), self::OPTION_TO, $to, $output)
        ) {
            return Command::INVALID;
        }

        $confirmation = (new SymfonyStyle($input, $output))
            ->confirm(
                sprintf('WARNING! Connection named "%s" schema and data will be PURGED. Do you want to continue?', $to),
                !$input->isInteractive()
            );

        if ($confirmation) {
            $this->schemaCopy->copy($source, $destination);

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    private function checkConnection(
        ?Connection $connection,
        string $arg,
        string $connectionName,
        OutputInterface $output,
    ): bool {
        $connectionName = trim($connectionName);

        if ('' === $connectionName) {
            $output->writeln('');
            $output->writeln(sprintf('"--%s" argument can not be empty', $arg));
            $output->writeln('');

            return false;
        }

        if (!$connection instanceof Connection) {
            $output->writeln('');
            $output->writeln(
                sprintf('Connection wanted to "--%s" argument: "%s" was not found!', $arg, $connectionName)
            );
            $output->writeln('');

            return false;
        }

        return true;
    }

    private function getConnection(string $connectionName): ?Connection
    {
        /* @var ?Connection $connection */
        try {
            $connection = $this->registry->getConnection($connectionName);
        } catch (InvalidArgumentException) {
            $connection = null;
        }

        return $connection;
    }
}
