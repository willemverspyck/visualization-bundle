<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Command;

use DateTimeImmutable;
use Exception;
use Spyck\VisualizationBundle\Event\ScheduleEvent;
use Spyck\VisualizationBundle\Repository\ScheduleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'spyck:visualization:schedule', description: 'Command for schedule events.')]
final class ScheduleCommand extends Command
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher, private readonly ScheduleRepository $scheduleRepository)
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new DateTimeImmutable();

        $schedules = $this->scheduleRepository->getSchedules();

        foreach ($schedules as $schedule) {
            $scheduleEvent = new ScheduleEvent($schedule, $date);

            $this->eventDispatcher->dispatch($scheduleEvent);
        }

        return Command::SUCCESS;
    }
}
