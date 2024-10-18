<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Preload;
use Spyck\VisualizationBundle\Entity\ScheduleInterface;

class PreloadRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Preload::class);
    }

    /**
     * @return array<int, Preload>
     */
    public function getPreloadsBySchedule(ScheduleInterface $schedule): array
    {
        return $this->createQueryBuilder('preload')
            ->innerJoin('preload.schedules', 'schedule', Join::WITH, 'schedule = :schedule')
            ->innerJoin('preload.dashboard', 'dashboard')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->where('preload.active = TRUE')
            ->setParameter('schedule', $schedule)
            ->getQuery()
            ->getResult();
    }
}
