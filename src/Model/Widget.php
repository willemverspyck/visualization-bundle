<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Spyck\ApiExtension\Model\Pagination;
use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Controller\WidgetController;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Widget
{
    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private array $data;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private int $total;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private array $fields;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private iterable $properties;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private iterable $events;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private array $filters;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private array $parameters;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private ?Pagination $pagination = null;

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): static
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return array<int, FieldInterface|MultipleFieldInterface>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    public function getProperties(): iterable
    {
        return $this->properties;
    }

    public function setProperties(iterable $properties): static
    {
        $this->properties = $properties;

        return $this;
    }

    public function getEvents(): iterable
    {
        return $this->events;
    }

    public function setEvents(iterable $events): static
    {
        $this->events = $events;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getPagination(): ?Pagination
    {
        return $this->pagination;
    }

    public function setPagination(?Pagination $pagination): static
    {
        $this->pagination = $pagination;

        return $this;
    }
}
