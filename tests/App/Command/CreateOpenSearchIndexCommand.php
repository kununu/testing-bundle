<?php
declare(strict_types=1);

namespace App\Command;

use OpenSearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:opensearch:create-index',
    description: 'Creates a new OpenSearch index'
)]
final class CreateOpenSearchIndexCommand extends Command
{
    public function __construct(private readonly Client $client)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('index_name', InputArgument::OPTIONAL, 'The name of the index to create', 'my_index');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->client->indices()->exists(['index' => $input->getArgument('index_name')])) {
            $this->client
                ->indices()
                ->create(['index' => $input->getArgument('index_name')]);
        }

        return Command::SUCCESS;
    }
}
