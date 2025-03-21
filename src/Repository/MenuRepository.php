<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\Menu;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Service\UserService;
use Spyck\VisualizationBundle\Utility\DataUtility;

class MenuRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly UserService $userService)
    {
        parent::__construct($managerRegistry, Menu::class);
    }

    /**
     * Get menu items with the user group permissions.
     *
     * @return array<int, Menu>
     */
    public function getMenus(): array
    {
        $user = $this->userService->getUser();

        $expr = new Expr();

        $queryBuilder = $this->createQueryBuilder('menu')
            ->addSelect('menuChildren')
            ->addSelect('COUNT(menuChildren) AS HIDDEN menuChildrenCount')
            ->leftJoin('menu.children', 'menuChildren', Join::WITH, $expr->andX($expr->eq('menuChildren.active', 'TRUE'), $expr->orX($expr->isNull('menuChildren.dashboard'), $expr->in('menuChildren.dashboard', $this->getMenuDashboardAsQueryBuilder($user, '1')->getDQL()))))
            ->where('menu.parent IS NULL')
            ->andWhere('menu.active = TRUE')
            ->andWhere($expr->orX($expr->isNull('menu.dashboard'), $expr->in('menu.dashboard', $this->getMenuDashboardAsQueryBuilder($user, '2')->getDQL())))
            ->groupBy('menu')
            ->addGroupBy('menuChildren')
            ->having('(menuChildrenCount > 0 AND menu.dashboard IS NULL) OR (menuChildrenCount = 0 AND menu.dashboard IS NOT NULL)')
            ->orderBy('menu.position')
            ->addOrderBy('menuChildren.position');

        if (null !== $user) {
            $queryBuilder
                ->setParameter('groups', $user->getGroups());
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, Menu>
     */
    public function getMenusByParent(?Menu $parent): array
    {
        $queryBuilder = $this->createQueryBuilder('menu')
            ->orderBy('menu.position');

        if (null === $parent) {
            $queryBuilder
                ->where('menu.parent IS NULL');
        } else {
            $queryBuilder
                ->where('menu.parent = :parent')
                ->setParameter('parent', $parent);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function patchMenu(Menu $menu, array $fields, ?int $position = null): void
    {
        if (in_array('position', $fields, true)) {
            DataUtility::assert(null !== $position);

            $menu->setPosition($position);
        }

        $this->getEntityManager()->persist($menu);
        $this->getEntityManager()->flush();
    }

    private function getMenuDashboardAsQueryBuilder(?UserInterface $user, string $index): QueryBuilder
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select(sprintf('dashboard_%s', $index))
            ->from(Dashboard::class, sprintf('dashboard_%s', $index))
            ->innerJoin(sprintf('dashboard_%s.blocks', $index), sprintf('block_%s', $index), Join::WITH, sprintf('block_%s.active = TRUE', $index))
            ->innerJoin(sprintf('block_%s.widget', $index), sprintf('widget_%s', $index), Join::WITH, sprintf('widget_%s.active = TRUE', $index))
            ->groupBy(sprintf('dashboard_%s', $index));

        if (null !== $user) {
            $queryBuilder
                ->innerJoin(sprintf('widget_%s.group', $index), sprintf('group_%s', $index), Join::WITH, sprintf('group_%s IN (:groups) AND group_%s.active = TRUE', $index, $index));
        }

        return $queryBuilder;
    }
}
