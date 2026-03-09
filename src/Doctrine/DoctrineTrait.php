<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Spyck\VisualizationBundle\Parameter\DateParameterInterface;
use Spyck\VisualizationBundle\Parameter\EntityParameterInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait DoctrineTrait
{
    private readonly EntityManagerInterface $entityManager;

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function getData(): iterable
    {
        if (false === $this instanceof DoctrineInterface) {
            throw new Exception(sprintf('"%s" must be instance of "%s"', __CLASS__, DoctrineInterface::class));
        }

        $queryBuilder = $this->getDataFromDoctrine();

        $filterLimit = $this->getFilterLimit();

        if (null !== $filterLimit) {
            $queryBuilder->setMaxResults($filterLimit);
        }

        $filterOffset = $this->getFilterOffset();

        if (null !== $filterOffset) {
            $queryBuilder->setFirstResult($filterOffset);
        }

        return $queryBuilder
            ->getQuery()
            ->useQueryCache(true)
            ->getArrayResult();
    }

    protected function getQueryBuilder(bool $autowire = true): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        if (false === $autowire) {
            return $queryBuilder;
        }

        foreach ($this->getParameterData() as $parameter) {
            if ($parameter instanceof DateParameterInterface) {
                $queryBuilder->setParameter($parameter->getName(), $parameter->getDataForQueryBuilder());
            }

            if ($parameter instanceof EntityParameterInterface) {
                $queryBuilder->setParameter($parameter->getName(), $parameter->getData());
            }
        }

        return $queryBuilder;
    }
}
