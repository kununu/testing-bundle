<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Command;

use Elasticsearch\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateElasticsearchIndexCommand extends Command
{
    public function __construct(private readonly Client $client)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:elasticsearch:create-index')
            ->setDescription('Creates a new Elasticsearch index')
            ->addArgument('index_name', InputArgument::OPTIONAL, 'The name of the index to create', 'my_index');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->client->indices()->exists(['index' => $input->getArgument('index_name')])) {
            $this->client
                ->indices()
                ->create(['index' => $input->getArgument('index_name')]);
        }

        return defined(sprintf('%s::SUCCESS', Command::class)) ? Command::SUCCESS : 0;
    }
}
