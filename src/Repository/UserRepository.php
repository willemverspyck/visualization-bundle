<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry, #[Autowire(param: 'spyck.visualization.user.class')] private readonly string $class)
    {
        parent::__construct($managerRegistry, $this->class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getUserById(int $id): ?UserInterface
    {
        return $this->createQueryBuilder('user')
            ->addSelect('groupAlias')
            ->leftJoin('user.groups', 'groupAlias', Join::WITH, 'groupAlias.active = TRUE')
            ->where('user.id = :id')
            ->andWhere('user.active = TRUE')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
