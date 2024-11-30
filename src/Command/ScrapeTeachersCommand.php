<?php

namespace App\Command;

use App\Service\ScraperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeTeachersCommand extends Command
{
    protected static $defaultName = 'teacher:scrape';
    private ScraperService $scraperService;

    // Inject the service via the constructor
    public function __construct(ScraperService $scraperService)
    {
        parent::__construct();
        $this->scraperService = $scraperService;
    }

    protected function configure() : void
    {
        // Set the command name here
        $this->setName('teacher:scrape')
            ->setDescription('Scrape teachers from external URL and store them in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Call the service's method
        $this->scraperService->scrapeTeachers();

        $output->writeln('Teachers scraped successfully.');

        return Command::SUCCESS;
    }
}
