<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Exception;
use Spyck\VisualizationBundle\Repository\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class RepositoryService
{
    public function __construct(#[AutowireLocator(services: 'spyck.visualization.repository')] private ServiceLocator $serviceLocator)
    {
    }

    /**
     * @throws Exception
     */
    public function getRepository(string $name): RepositoryInterface
    {
        $repository = array_find($this->getRepositories(), fn (RepositoryInterface $repository) => $repository->getVisualizationName() === $name);

        if (null === $repository) {
            throw new Exception(sprintf('Repository "%s" not found', $name));
        }

        return $repository;
    }

    /**
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function getEntityById(string $entityName, string $entityId): ?object
    {
        return $this->getRepository($entityName)->getVisualizationEntityById($entityId);
    }

    /**
     * @return array<string, RepositoryInterface>
     */
    private function getRepositories(): array
    {
        return iterator_to_array($this->serviceLocator->getIterator());
    }
}
