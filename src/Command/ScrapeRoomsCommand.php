<?php

namespace App\Command;

use App\Service\ScraperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeRoomsCommand extends Command
{
    private ScraperService $scraperService;

    public function __construct(ScraperService $scraperService)
    {
        parent::__construct();
        $this->scraperService = $scraperService;
    }

    protected function configure() : void
    {
        $this->setName('scrape:room');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->scraperService->scrapeRooms();

        $output->writeln('Rooms scraped successfully.');

        return Command::SUCCESS;
    }
}
