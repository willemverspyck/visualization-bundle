<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Schedule;

class ScheduleRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Schedule::class);
    }

    public function getScheduleDataByDate(DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('schedule')
            ->where('schedule.modules IS EMPTY')
            ->andWhere('JSON_LENGTH(schedule.hours) = 0 OR JSON_CONTAINS(schedule.hours, :hours) = TRUE')
            ->andWhere('JSON_LENGTH(schedule.days) = 0 OR JSON_CONTAINS(schedule.days, :days) = TRUE')
            ->andWhere('JSON_LENGTH(schedule.weeks) = 0 OR JSON_CONTAINS(schedule.weeks, :weeks) = TRUE')
            ->andWhere('JSON_LENGTH(schedule.weekdays) = 0 OR JSON_CONTAINS(schedule.weekdays, :weekdays) = TRUE')
            ->setParameter('hours', sprintf('%d', $date->format('G')))
            ->setParameter('days', sprintf('%d', $date->format('j')))
            ->setParameter('weeks', sprintf('%d', $date->format('W')))
            ->setParameter('weekdays', sprintf('%d', $date->format('N')))
            ->getQuery()
            ->getResult();
    }
}
