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

    /**
     * @return array<int, Schedule>
     */
    public function getScheduleData(): array
    {
        return $this->createQueryBuilder('schedule')
            ->getQuery()
            ->getResult();
    }
}
