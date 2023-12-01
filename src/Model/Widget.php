<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Spyck\ApiExtension\Model\Pagination;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Widget
{
    #[Serializer\Groups('spyck:visualization:widget:item')]
    private iterable $data;

    #[Serializer\Groups('spyck:visualization:widget:item')]
    private iterable $fields;

    private iterable $properties;
    private iterable $events;
    private iterable $filters;
    private iterable $parameters;

    #[Serializer\Groups('spyck:visualization:widget:item')]
    private ?Pagination $pagination = null;

    public function getData(): iterable
    {
        return $this->data;
    }

    public function setData(iterable $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getFields(): iterable
    {
        return $this->fields;
    }

    public function setFields(iterable $fields): static
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

    public function getFilters(): iterable
    {
        return $this->filters;
    }

    public function setFilters(iterable $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function getParameters(): iterable
    {
        return $this->parameters;
    }

    public function setParameters(iterable $parameters): static
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
