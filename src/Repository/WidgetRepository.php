<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Widget;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WidgetRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly TokenStorageInterface $tokenStorage)
    {
        parent::__construct($managerRegistry, Widget::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getWidgetById(int $id): ?Widget
    {
        return $this->getWidgetQueryBuilder()
            ->andWhere('widget.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getWidgetByAdapter(string $adapter): ?Widget
    {
        return $this->getWidgetQueryBuilder()
            ->andWhere('widget.adapter = :adapter')
            ->setParameter('adapter', $adapter)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function getWidgetQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('widget')
            ->where('widget.active = TRUE');

        $user = $this->getUserByToken($this->tokenStorage->getToken());

        if (null !== $user) {
            $queryBuilder
                ->addSelect('groupRequired')
                ->addSelect('groupOptional')
                ->innerJoin('widget.group', 'groupRequired', Join::WITH, 'groupRequired IN (:groups) AND groupRequired.active = TRUE')
                ->leftJoin('widget.groups', 'groupOptional', Join::WITH, 'groupOptional.active = TRUE')
                ->setParameter('groups', $user->getGroups());
        }

        return $queryBuilder;
    }
}
