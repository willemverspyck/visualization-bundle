<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event\Subscriber;

use Spyck\VisualizationBundle\Event\FilterEvent;
use Spyck\VisualizationBundle\Filter\DateFilterInterface;
use Spyck\VisualizationBundle\Filter\EntityFilterInterface;
use Spyck\VisualizationBundle\Filter\OptionFilterInterface;
use Spyck\VisualizationBundle\Service\RepositoryService;
use Spyck\VisualizationBundle\Widget\WidgetInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FilterEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly RepositoryService $repositoryService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FilterEvent::class => [
                'onFilter',
            ],
        ];
    }

    public function onFilter(FilterEvent $event): void
    {
        $filter = $event->getFilter();

        $options = match (true) {
            $filter instanceof DateFilterInterface => $this->getFilterDate($filter),
            $filter instanceof EntityFilterInterface => $this->getFilterEntity($filter, $event->getWidget()),
            $filter instanceof OptionFilterInterface => $this->getFilterOption($filter),
            default => [],
        };

        $event->setOptions($options);
    }

    private function getFilterDate(DateFilterInterface $filter): array
    {
        $dataAsObject = $filter->getDataAsObject();

        if (null === $dataAsObject) {
            return [];
        }

        return $dataAsObject;
    }

    private function getFilterEntity(EntityFilterInterface $filter, WidgetInterface $widget): array
    {
        $data = [];

        $dataAsObject = $filter->getDataAsObject();

        if (null !== $dataAsObject) {
            $data = array_map(function (object $object): int {
                return $object->getId();
            }, $dataAsObject);
        }

        $options = [];

        $entities = $this->repositoryService->getRepository($filter->getName())->getVisualizationEntities($widget);

        foreach ($entities as $entity) {
            $options[] = [
                'id' => $entity->getId(),
                'parent' => null,
                'name' => $entity->getName(),
                'select' => in_array($entity->getId(), $data, true),
            ];
        }

        return $options;
    }

    private function getFilterOption(OptionFilterInterface $filter): array
    {
        $data = $filter->getData();

        if (null === $data) {
            $data = [];
        }

        $options = [];

        foreach ($filter->getOptions() as $id => $name) {
            $options[] = [
                'id' => $id,
                'parent' => null,
                'name' => $name,
                'select' => in_array($id, $data, true),
            ];
        }

        return $options;
    }
}
