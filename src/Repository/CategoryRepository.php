<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Category;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategoryRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly TokenStorageInterface $tokenStorage)
    {
        parent::__construct($managerRegistry, Category::class);
    }

    public function getCategoryData(): array
    {
        $queryBuilder = $this->createQueryBuilder('category')
            ->addSelect('dashboard')
            ->addSelect('block')
            ->addSelect('widget')
            ->innerJoin('category.dashboards', 'dashboard', Join::WITH, 'dashboard.active = TRUE')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE');

        $user = $this->getUserByToken($this->tokenStorage->getToken());

        if (null !== $user) {
            $queryBuilder
                ->innerJoin('widget.group', 'groupRequired', Join::WITH, 'groupRequired IN (:groups) AND groupRequired.active = TRUE')
                ->setParameter('groups', $user->getGroups());
        }

        return $queryBuilder
            ->where('category.active = TRUE')
            ->orderBy('category.name')
            ->addOrderBy('dashboard.name')
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }
}
