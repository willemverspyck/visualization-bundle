<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Mail;
use Spyck\VisualizationBundle\Entity\Schedule;

class MailRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Mail::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getMailByCode(string $code): ?Mail
    {
        return $this->createQueryBuilder('mail')
            ->innerJoin('mail.dashboard', 'dashboard')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->leftJoin('mail.users', 'user')
            ->where('mail.code = :code')
            ->andWhere('mail.active = TRUE')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, Mail>
     */
    public function getMailDataBySchedule(Schedule $schedule): array
    {
        return $this->createQueryBuilder('mail')
            ->innerJoin('mail.schedules', 'schedule', Join::WITH, 'schedule = :schedule')
            ->innerJoin('mail.dashboard', 'dashboard')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->innerJoin('mail.users', 'user')
            ->where('mail.active = TRUE')
            ->setParameter('schedule', $schedule)
            ->getQuery()
            ->getResult();
    }
}
