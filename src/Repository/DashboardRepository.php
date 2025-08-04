<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Service\UserService;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DashboardRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly UserService $userService)
    {
        parent::__construct($managerRegistry, Dashboard::class);
    }

    /**
     * @throws AuthenticationException
     * @throws NonUniqueResultException
     */
    public function getDashboardById(int $id, bool $authentication = true): ?Dashboard
    {
        return $this->getDashboardAsQueryBuilder($authentication)
            ->andWhere('dashboard.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws AuthenticationException
     * @throws NonUniqueResultException
     */
    public function getDashboardByCode(string $code, bool $authentication = true): ?Dashboard
    {
        return $this->getDashboardAsQueryBuilder($authentication)
            ->andWhere('dashboard.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, Dashboard>
     *
     * @throws AuthenticationException
     */
    public function getDashboardsByUser(): array
    {
        $user = $this->userService->getUser();

        if (null === $user) {
            return [];
        }

        return $this->getDashboardAsQueryBuilder(true)
            ->innerJoin('dashboard.user', 'user', Join::WITH, 'user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    private function getDashboardAsQueryBuilder(bool $authentication): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('dashboard')
            ->addSelect('block')
            ->addSelect('widget')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->where('dashboard.active = TRUE')
            ->orderBy('dashboard.timestampCreated', 'DESC')
            ->addOrderBy('block.position');

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
}
