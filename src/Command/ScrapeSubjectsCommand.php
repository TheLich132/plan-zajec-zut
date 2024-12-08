<?php

namespace App\Command;

use App\Service\ScraperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeSubjectsCommand extends Command
{
    private ScraperService $scraperService;

    public function __construct(ScraperService $scraperService)
    {
        parent::__construct();
        $this->scraperService = $scraperService;
    }

    protected function configure() : void
    {
        $this->setName('scrape:subject');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->scraperService->scrapeSubjects();

        $output->writeln('Subjects scraped successfully.');

        return Command::SUCCESS;
    }
}
