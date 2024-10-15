<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Schedule;

class ScheduleRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Schedule::class);
    }

    public function getScheduleByCode(string $code): ?Schedule
    {
        return $this->createQueryBuilder('schedule')
            ->where('schedule.code = :code')
            ->andWhere('schedule.active = TRUE')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, Schedule>
     */
    public function getSchedules(): array
    {
        return $this->createQueryBuilder('schedule')
            ->where('schedule.active = TRUE')
            ->getQuery()
            ->getResult();
    }
}
