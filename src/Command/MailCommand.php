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

        $schedules = $this->scheduleRepository->getScheduleDataByDate($date);

        foreach ($schedules as $schedule) {
            $this->mailService->handleMailMessageBySchedule($schedule);
        }

        return Command::SUCCESS;
    }
}
