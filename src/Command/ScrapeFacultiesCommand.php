<?php

namespace App\Command;

use App\Entity\Subject;
use App\Service\ScraperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeFacultiesCommand extends Command
{
    private ScraperService $scraperService;

    // Inject the service via the constructor
    public function __construct(ScraperService $scraperService)
    {
        parent::__construct();
        $this->scraperService = $scraperService;
    }

    protected function configure(): void
    {
        $this->setName('scrape:faculty');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->scraperService->isDataEmpty(Subject::class)) {
            ini_set('memory_limit', '512M');
            $this->scraperService->scrapeSubjects();
        }

        $this->scraperService->scrapeFaculties();

        $output->writeln('Faculties scraped successfully.');

        return Command::SUCCESS;
    }
}
