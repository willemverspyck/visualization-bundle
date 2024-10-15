<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\Schedule;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DashboardRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly TokenStorageInterface $tokenStorage)
    {
        parent::__construct($managerRegistry, Dashboard::class);
    }

    /**
     * @throws AuthenticationException
     * @throws NonUniqueResultException
     */
    public function getDashboardById(int $id, bool $authentication = true): ?Dashboard
    {
        return $this->getDashboardQueryBuilder($authentication)
            ->andWhere('dashboard.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }

    /**
     * @throws AuthenticationException
     * @throws NonUniqueResultException
     */
    public function getDashboardByCode(string $code): ?Dashboard
    {
        return $this->getDashboardQueryBuilder()
            ->andWhere('dashboard.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, Dashboard>
     *
     * @throws AuthenticationException
     */
    public function getDashboardsByUser(): array
    {
        $user = $this->getUserByToken($this->tokenStorage->getToken());

        if (null === $user) {
            return [];
        }

        return $this->getDashboardQueryBuilder()
            ->leftJoin('dashboard.user', 'user', Join::WITH, 'user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }

    private function getDashboardQueryBuilder(bool $authentication = true): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('dashboard')
            ->addSelect('block')
            ->addSelect('widget')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->where('dashboard.active = TRUE')
            ->orderBy('dashboard.timestampCreated', Criteria::DESC)
            ->addOrderBy('block.position');

        if ($authentication) {
            $user = $this->getUserByToken($this->tokenStorage->getToken());

            if (null !== $user) {
                $queryBuilder
                    ->innerJoin('widget.group', 'groupRequired', Join::WITH, 'groupRequired IN (:groups) AND groupRequired.active = TRUE')
                    ->setParameter('groups', $user->getGroups());
            }
        }

        return $queryBuilder;
    }
}
