<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Mail;
use Spyck\VisualizationBundle\Entity\ScheduleInterface;

class MailRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Mail::class);
    }

    /**
     * @return array<int, Mail>
     */
    public function getMailsBySchedule(ScheduleInterface $schedule): array
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
