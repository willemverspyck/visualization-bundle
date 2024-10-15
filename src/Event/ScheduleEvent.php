<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event;

use DateTimeImmutable;
use Spyck\VisualizationBundle\Entity\Schedule;
use Symfony\Contracts\EventDispatcher\Event;

final class ScheduleEvent extends Event
{
    public function __construct(private readonly Schedule $schedule, private readonly DateTimeImmutable $date)
    {
    }

    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }
}
