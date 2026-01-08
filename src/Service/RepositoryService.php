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
    public function __construct(#[AutowireLocator(services: 'spyck.visualization.repository', defaultIndexMethod: 'getVisualizationName')] private ServiceLocator $serviceLocator)
    {
    }

    /**
     * @throws Exception
     */
    public function getRepository(string $name): RepositoryInterface
    {
        return $this->serviceLocator->get($name);
    }

    /**
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function getEntityById(string $entityName, int $entityId): ?object
    {
        return $this->getRepository($entityName)->getVisualizationEntityById($entityId);
    }
}
