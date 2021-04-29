<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Command;

use Elasticsearch\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateElasticsearchIndexCommand extends Command
{
    protected static $defaultName = 'app:elasticsearch:create-index';

    private $elasticsearchClient;

    public function __construct(Client $elasticsearchClient)
    {
        $this->elasticsearchClient = $elasticsearchClient;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new Elasticsearch index')
            ->addArgument('index_name', InputArgument::OPTIONAL, 'The name of the index to create', 'my_index');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->elasticsearchClient
            ->indices()
            ->create(['index' => $input->getArgument('index_name')]);

        return Command::SUCCESS;
    }
}
