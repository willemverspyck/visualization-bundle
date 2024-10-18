<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\ScheduleInterface;

class ScheduleRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Schedule::class);
    }

    public function getScheduleByCode(string $discriminator, string $code): ?ScheduleInterface
    {
        return $this->createQueryBuilder('schedule')
            ->where('schedule INSTANCE OF :distriminator')
            ->andWhere('schedule.code = :code')
            ->andWhere('schedule.active = TRUE')
            ->setParameter('discriminator', $discriminator)
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, ScheduleInterface>
     */
    public function getSchedules(string $discriminator): array
    {
        return $this->createQueryBuilder('schedule')
            ->where('schedule INSTANCE OF :distriminator')
            ->andWhere('schedule.active = TRUE')
            ->setParameter('discriminator', $discriminator)
            ->getQuery()
            ->getResult();
    }
}
