<?php

namespace App\Command;

use App\Service\ScraperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeAllCommand extends Command
{
    private ScraperService $scraperService;

    public function __construct(ScraperService $scraperService)
    {
        parent::__construct();
        $this->scraperService = $scraperService;
    }

    protected function configure() : void
    {
        $this->setName('scrape:all');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '512M');

        $this->scraperService->scrapeRooms();
        $output->writeln('Rooms scraped successfully.');

        $this->scraperService->scrapeSubjects();
        $output->writeln('Subjects scraped successfully.');

        $this->scraperService->scrapeTeachers();
        $output->writeln('Teachers scraped successfully.');

        $this->scraperService->scrapeLessons();
        $output->writeln('Lessons scraped successfully.');

        $this->scraperService->scrapeStudents();
        $output->writeln('Students scraped successfully.');

        return Command::SUCCESS;
    }
}
