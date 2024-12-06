<?php

namespace App\Command;

use App\Service\ScraperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteAllCommand extends Command
{
    private ScraperService $scraperService;

    public function __construct(ScraperService $scraperService)
    {
        parent::__construct();
        $this->scraperService = $scraperService;
    }

    protected function configure() : void
    {
        $this->setName('delete:all')
            ->setDescription('Deletes all data from the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Are you sure you want to delete all data from the database?');
        $output->writeln('This action cannot be undone.');
        $output->writeln('Type "yes" to confirm:');
        $confirmation = trim(fgets(STDIN));

        if ($confirmation !== 'yes') {
            $output->writeln('Deletion cancelled.');
            return Command::SUCCESS;
        }

        $this->scraperService->deleteAll();
        $output->writeln('All data has been deleted.');
        return Command::SUCCESS;
    }
}
