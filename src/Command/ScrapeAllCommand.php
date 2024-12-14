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
        ini_set('memory_limit', '-1');

        $maxRetries = 10;
        $retryDelay = 5; // seconds

        $tasks = [
            ['method' => 'scrapeRooms', 'message' => 'Rooms scraped successfully.'],
            ['method' => 'scrapeSubjects', 'message' => 'Subjects scraped successfully.'],
            ['method' => 'scrapeTeachers', 'message' => 'Teachers scraped successfully.'],
            ['method' => 'scrapeLessons', 'message' => 'Lessons scraped successfully.'],
            ['method' => 'scrapeStudents', 'message' => 'Students scraped successfully.'],
        ];

        foreach ($tasks as $task) {
            $success = false;
            $attempts = 0;

            while (!$success && $attempts < $maxRetries) {
                try {
                    $attempts++;
                    $this->scraperService->{$task['method']}();
                    $output->writeln($task['message']);
                    $success = true;
                } catch (\Exception $e) {
                    $output->writeln("<error>Failed to execute {$task['method']} on attempt $attempts: {$e->getMessage()}</error>");

                    if ($attempts < $maxRetries) {
                        $output->writeln("Retrying in $retryDelay seconds...");
                        sleep($retryDelay);
                    } else {
                        $output->writeln("<error>Max retries reached for {$task['method']}. Skipping...</error>");
                    }
                }
            }
        }

        return Command::SUCCESS;
    }

}
