<?php

namespace App\Command;

use App\Service\ScraperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeTeachersCommand extends Command
{
    private ScraperService $scraperService;

    // Inject the service via the constructor
    public function __construct(ScraperService $scraperService)
    {
        parent::__construct();
        $this->scraperService = $scraperService;
    }

    protected function configure() : void
    {
        $this->setName('scrape:teacher');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->scraperService->scrapeTeachers();

        $output->writeln('Teachers scraped successfully.');

        return Command::SUCCESS;
    }
}
