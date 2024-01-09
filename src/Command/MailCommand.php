<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Command;

use DateTime;
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
        $date = new DateTime();

        $schedules = $this->scheduleRepository->getScheduleData($date);

        foreach ($schedules as $schedule) {
            if ($this->match($schedule->getHours(), $date->format('G')) && $this->match($schedule->getDays(), $date->format('j')) && $this->match($schedule->getWeeks(), $date->format('W')) && $this->match($schedule->getWeekdays(),$date->format('N'))) {
                $this->mailService->handleMailMessageBySchedule($schedule);
            }
        }

        return Command::SUCCESS;
    }

    private function match(array $data, string $value): bool
    {
        return 0 === count($data) || in_array($value, $data, true);
    }
}
