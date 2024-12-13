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

    public function __construct(FilterService $filterService)
    {
        parent::__construct();
        $this->filterService = $filterService;
    }

    protected function configure(): void
    {
        $this->setName('filter')
            ->setDescription('Filters lessons based on provided criteria')
            ->addArgument('room', InputArgument::OPTIONAL, 'Room name')
            ->addArgument('teacher', InputArgument::OPTIONAL, 'Teacher name')
            ->addArgument('group', InputArgument::OPTIONAL, 'Group name')
            ->addArgument('subject', InputArgument::OPTIONAL, 'Subject name')
            ->addArgument('student', InputArgument::OPTIONAL, 'Student index')
            ->addArgument('faculty', InputArgument::OPTIONAL, 'Faculty name')
            ->addArgument('isStationary', InputArgument::OPTIONAL, 'Is stationary')
            ->addArgument('form', InputArgument::OPTIONAL, 'Form name');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $room = $input->getArgument('room') ?? '';
        $teacher = $input->getArgument('teacher') ?? '';
        $group = $input->getArgument('group') ?? '';
        $subject = $input->getArgument('subject') ?? '';
        $student = $input->getArgument('student') ?? '';
        $faculty = $input->getArgument('faculty') ?? '';
        $isStationary = $input->getArgument('isStationary') === 't';
        $form = $input->getArgument('form') ?? '';
        $start = new \DateTime('2020-12-02 00:00:00');
        $finish = new \DateTime('2030-12-08 23:59:59');

        $lessons = $this->filterService->filter(
            faculty: $faculty,
            teacher: $teacher,
            room: $room,
            subject: $subject,
            group: $group,
            student: $student,
            isStationary: $isStationary,
            form: $form,
            start: $start,
            finish: $finish);

        $output->writeln(
            "
            Faculty: $faculty,
            Teacher: $teacher,
            Room: $room,
            Subject: $subject,
            Group: $group,
            Student: $student,
            Is stationary: " . ($isStationary ? 'true' : 'false') . ",
            Form: $form,
            Start: " . $start->format('Y-m-d H:i:s') . ",
            Finish: " . $finish->format('Y-m-d H:i:s'));


        foreach ($lessons as $lesson) {
            $output->writeln($lesson->getName());
        }

        return Command::SUCCESS;
    }
}
