<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Category;
use Spyck\VisualizationBundle\Map\CategoryMap;
use Spyck\VisualizationBundle\Service\UserService;

class CategoryRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly UserService $userService)
    {
        parent::__construct($managerRegistry, Category::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getCategoryById(int $id, bool $authentication = true): ?Category
    {
        return $this->getCategoriesAsQueryBuilder($authentication)
            ->andWhere('category.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCategoriesByMapAsQueryBuilder(CategoryMap $categoryMap): QueryBuilder
    {
        return $this->getCategoriesAsQueryBuilder(true)
            ->orderBy('category.position');
    }

    private function getCategoriesAsQueryBuilder(bool $authentication): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('category')
            ->addSelect('dashboard')
            ->addSelect('block')
            ->addSelect('widget')
            ->innerJoin('category.dashboards', 'dashboard', Join::WITH, 'dashboard.active = TRUE')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->where('category.active = TRUE');

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
