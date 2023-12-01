<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Countable;
use Exception;
use IteratorAggregate;
use Spyck\VisualizationBundle\Repository\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class RepositoryService
{
    /**
     * @param Countable&IteratorAggregate $repositories
     */
    public function __construct(#[TaggedIterator(tag: 'spyck.visualization.repository', defaultIndexMethod: 'getVisualizationName')] private iterable $repositories)
    {
    }

    /**
     * @throws Exception
     */
    public function getRepository(string $name): RepositoryInterface
    {
        foreach ($this->getRepositories() as $index => $repository) {
            if ($index === $name) {
                return $repository;
            }
        }

        throw new NotFoundHttpException(sprintf('Repository "%s" does not exist', $name));
    }

    /**
     * @return iterable<string, RepositoryInterface>
     *
     * @throws Exception
     */
    public function getRepositories(): iterable
    {
        return $this->repositories->getIterator();
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
