<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
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

    public function getMailById(int $id): ?Mail
    {
        return $this->createQueryBuilder('mail')
            ->innerJoin('mail.dashboard', 'dashboard')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->leftJoin('mail.users', 'user')
            ->where('mail.id = :id')
            ->andWhere('mail.active = TRUE')
            ->andWhere('mail.subscribe = TRUE')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
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

    public function getMailsBySubscribe(bool $subscribe): array
    {
        return $this->createQueryBuilder('mail')
            ->innerJoin('mail.dashboard', 'dashboard')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->leftJoin('mail.users', 'user')
            ->where('mail.active = TRUE')
            ->andWhere('mail.subscribe = :subscribe')
            ->setParameter('subscribe', $subscribe)
            ->getQuery()
            ->getResult();
    }

    public function patchMail(Mail $mail, array $fields, ?ArrayCollection $users = null): void
    {
        if (in_array('users', $fields, true) && null !== $users) {
            $this->patchCollection($mail->getUsers(), $users);
        }

        $this->getEntityManager()->persist($mail);
        $this->getEntityManager()->flush();
    }
}
