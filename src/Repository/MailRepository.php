<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Mail;
use Spyck\VisualizationBundle\Entity\ScheduleInterface;
use Spyck\VisualizationBundle\Service\UserService;

class MailRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly UserService $userService)
    {
        parent::__construct($managerRegistry, Mail::class);
    }

    public function getMailById(int $id): ?Mail
    {
        return $this->getMailAsQueryBuilder(true)
            ->andWhere('mail.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<Mail>
     */
    public function getMailsBySchedule(ScheduleInterface $schedule): array
    {
        return $this->getMailAsQueryBuilder(false)
            ->innerJoin('mail.schedules', 'schedule', Join::WITH, 'schedule = :schedule')
            ->setParameter('schedule', $schedule)
            ->getQuery()
            ->getResult();
    }

    public function getMailsBySubscribe(bool $subscribe): array
    {
        return $this->getMailAsQueryBuilder(true)
            ->andWhere('mail.subscribe = :subscribe')
            ->setParameter('subscribe', $subscribe)
            ->getQuery()
            ->getResult();
    }

    private function getMailAsQueryBuilder(bool $authentication): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('mail')
            ->innerJoin('mail.dashboard', 'dashboard')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->leftJoin('mail.users', 'user')
            ->where('mail.active = TRUE');

        if (false === $authentication) {
            return $queryBuilder;
        }

        $user = $this->userService->getUser();

        if (null === $user) {
            return $queryBuilder;
        }

        return $queryBuilder
            ->innerJoin('widget.group', 'groupRequired', Join::WITH, 'groupRequired IN (:groups) AND groupRequired.active = TRUE')
            ->setParameter('groups', $user->getGroups());
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
