<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Command;

use DateTimeImmutable;
use Exception;
use Spyck\VisualizationBundle\Repository\ScheduleRepository;
use Spyck\VisualizationBundle\Service\MailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'spyck:visualization:mail', description: 'Command for schedule the mail message.')]
final class MailCommand extends Command
{
    public function __construct(private readonly MailService $mailService, private readonly ScheduleRepository $scheduleRepository)
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new DateTimeImmutable();

        $schedules = $this->scheduleRepository->getScheduleData($date);

        foreach ($schedules as $schedule) {
            $hour = (int) $date->format('G');
            $day = (int) $date->format('j');
            $week = (int) $date->format('W');
            $weekday = (int) $date->format('N');

            if ($this->match($schedule->getHours(), $hour) && $this->match($schedule->getDays(), $day) && $this->match($schedule->getWeeks(), $week) && $this->match($schedule->getWeekdays(), $weekday)) {
                $this->mailService->executeMailMessageBySchedule($schedule);
            }
        }

        return Command::SUCCESS;
    }

    private function match(array $data, int $value): bool
    {
        return 0 === count($data) || in_array($value, $data, true);
    }
}
