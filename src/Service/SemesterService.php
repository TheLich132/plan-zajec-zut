<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

/** example usage
 * $semesterService = new SemesterService($semesterConfig);
 * $currentYear = $semesterService->getCurrentYear();
 * $previousYear = $semesterService->getPreviousYear();
 * $currentSemester = $semesterService->getCurrentSemester();
 * $previousSemester = $semesterService->getPreviousSemester();
 */
class SemesterService
{
    private array $semesterConfig;

    public function __construct(string $configPath)
    {
        $this->semesterConfig = Yaml::parseFile($configPath)['semester'];
    }


    public function getCurrentYear(): array
    {
        return $this->semesterConfig['current'];
    }

    public function getPreviousYear(): array
    {
        return $this->semesterConfig['previous'];
    }

    public function getCurrentSemester(): array
    {
        $currentYear = $this->getCurrentYear();
        $today = new \DateTime();

        if ($this->isDateInRange($today, $currentYear['winter_semester'])) {
            return [
                'semester' => 'winter',
                'year' => $currentYear['year'],
                'details' => $currentYear['winter_semester']
            ];
        }

        if ($this->isDateInRange($today, $currentYear['summer_semester'])) {
            return [
                'semester' => 'summer',
                'year' => $currentYear['year'],
                'details' => $currentYear['summer_semester']
            ];
        }

        return [];
    }

    public function getPreviousSemester(): array
    {
        $currentSemester = $this->getCurrentSemester();

        if ($currentSemester['semester'] === 'winter') {
            return [
                'semester' => 'summer',
                'year' => explode('/', $currentSemester['year'])[0] - 1 . '/' . explode('/', $currentSemester['year'])[1] - 1,
                'details' => $this->getPreviousYear()['summer_semester']
            ];
        }

        if ($currentSemester['semester'] === 'summer') {
            return [
                'semester' => 'winter',
                'year' => $currentSemester['year'],
                'details' => $this->getPreviousYear()['winter_semester']
            ];
        }

        return [];
    }

    private function isDateInRange(\DateTime $date, array $range): bool
    {
        $startDate = new \DateTime($range['start_date']);
        $endDate = new \DateTime($range['end_date']);
        return $date >= $startDate && $date <= $endDate;
    }
}

