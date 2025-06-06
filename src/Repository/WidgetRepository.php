<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Widget;
use Spyck\VisualizationBundle\Service\UserService;

class WidgetRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly UserService $userService)
    {
        parent::__construct($managerRegistry, Widget::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getWidgetById(int $id, bool $authentication = true): ?Widget
    {
        return $this->getWidgetAsQueryBuilder($authentication)
            ->andWhere('widget.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getWidgetByAdapter(string $adapter, bool $authentication = true): ?Widget
    {
        return $this->getWidgetAsQueryBuilder($authentication)
            ->andWhere('widget.adapter = :adapter')
            ->setParameter('adapter', $adapter)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function getWidgetAsQueryBuilder(bool $authentication): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('widget')
            ->where('widget.active = TRUE');

        if (false === $authentication) {
            return $queryBuilder;
        }

        $user = $this->userService->getUser();

        if (null === $user) {
            return $queryBuilder;
        }

        return $queryBuilder
           ->addSelect('groupRequired')
           ->addSelect('groupOptional')
           ->innerJoin('widget.group', 'groupRequired', Join::WITH, 'groupRequired IN (:groups) AND groupRequired.active = TRUE')
           ->leftJoin('widget.groups', 'groupOptional', Join::WITH, 'groupOptional.active = TRUE')
           ->setParameter('groups', $user->getGroups());
    }
}
