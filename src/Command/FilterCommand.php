<?php

namespace App\Command;

use App\Service\FilterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FilterCommand extends Command
{
    private FilterService $filterService;

    // Inject the service via the constructor
    public function __construct(FilterService $filterService)
    {
        parent::__construct();
        $this->filterService = $filterService;
    }

    protected function configure() : void
    {
        $this->setName('filter')
            ->setDescription('Filters lessons based on provided criteria')
            ->addArgument('room', InputArgument::OPTIONAL, 'Room name')
            ->addArgument('teacher', InputArgument::OPTIONAL, 'Teacher name')
            ->addArgument('group', InputArgument::OPTIONAL, 'Group name')
            ->addArgument('subject', InputArgument::OPTIONAL, 'Subject name');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $room = $input->getArgument('room') ?? '';
        $teacher = $input->getArgument('teacher') ?? '';
        $group = $input->getArgument('group') ?? '';
        $subject = $input->getArgument('subject') ?? '';
        // call the service to filter the lessons
        $lessons = $this->filterService->filter(teacher: $teacher, room: $room, group: $group, subject: $subject);

        $output->writeln("INPUT: Room: $room, Teacher: $teacher, Group: $group, Subject: $subject");

        // output the lessons
        foreach ($lessons as $lesson) {
            $output->writeln($lesson->getName());
        }

        return Command::SUCCESS;
    }
}
