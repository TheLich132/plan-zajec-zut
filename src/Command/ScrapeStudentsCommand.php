<?php

namespace App\Command;

use App\Service\ScraperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeStudentsCommand extends Command
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
        $this->setName('scrape:student');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1');

        //unused, because there are 10 attempts for each student

//        $maxRetries = 10;
//        $retryDelay = 5; // seconds
//        $attempts = 0;
//        $success = false;
//        while(!$success && $attempts < $maxRetries) {
//            try {
//                $attempts++;
//                $this->scraperService->scrapeStudents();
//                $output->writeln('Students scraped successfully.');
//                $success = true;
//            } catch (\Exception $e) {
//                $output->writeln("<error>Failed to execute scrapeStudents on attempt $attempts: {$e->getMessage()}</error>");
//                if ($attempts < $maxRetries) {
//                    $output->writeln("Retrying in $retryDelay seconds...");
//                    sleep($retryDelay);
//                } else {
//                    $output->writeln("<error>Max retries reached for scrapeStudents. Skipping...</error>");
//                }
//            }
//        }
        $this->scraperService->scrapeStudents();
        $output->writeln('Students scraped successfully.');

        return Command::SUCCESS;
    }
}
