<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Widget;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Func;
use Exception;
use Spyck\VisualizationBundle\Entity\Widget;
use Spyck\VisualizationBundle\Filter\EntityFilterInterface;
use Spyck\VisualizationBundle\Filter\FilterInterface;
use Spyck\VisualizationBundle\Filter\LimitFilter;
use Spyck\VisualizationBundle\Filter\OffsetFilter;
use Spyck\VisualizationBundle\Filter\OptionFilter;
use Spyck\VisualizationBundle\Parameter\DateParameterInterface;
use Spyck\VisualizationBundle\Parameter\EntityParameterInterface;
use Spyck\VisualizationBundle\Parameter\ParameterInterface;

abstract class AbstractWidget implements WidgetInterface
{
    private const CACHE = 3600;

    private ?string $view = null;
    private Widget $widget;

    /**
     * @var array|FilterInterface[]
     */
    private array $filters = [];

    /**
     * @var array|DateParameterInterface[]|EntityParameterInterface[]
     */
    private array $parameters = [];

    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * @return array<int, FilterInterface>
     */
    public function getFilterData(): array
    {
        return $this->filters;
    }

    public function getFilterDataRequest(): array
    {
        $content = [];

        foreach ($this->getFilterData() as $filter) {
            $content[$filter->getField()] = $filter->getData();
        }

        return $content;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array<int, ParameterInterface>
     */
    public function getParameterData(): array
    {
        return $this->parameters;
    }

    public function getParameterDataRequest(): array
    {
        $data = [];

        foreach ($this->getParameterData() as $parameter) {
            if ($parameter instanceof DateParameterInterface) {
                $data[$parameter->getField()] = $parameter->getDataForRequest();
            }

            if ($parameter instanceof EntityParameterInterface) {
                $data[$parameter->getField()] = $parameter->getData();
            }
        }

        return $data;
    }

    public function getFilter(string $name): ?array
    {
        if (false === array_key_exists($name, $this->filters)) {
            return null;
        }

        $filter = $this->filters[$name];

        if ($filter instanceof FilterInterface) {
            if ($filter instanceof EntityFilterInterface) {
                return $filter->getDataAsObject();
            }

            return $filter->getData();
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function getParameter(string $name): object
    {
        if (false === array_key_exists($name, $this->parameters)) {
            throw new Exception(sprintf('Parameter "%s" not found', $name));
        }

        $parameter = $this->parameters[$name];

        if ($parameter instanceof DateParameterInterface) {
            return $parameter->getData();
        }

        if ($parameter instanceof EntityParameterInterface) {
            return $parameter->getDataAsObject();
        }

        throw new Exception(sprintf('Parameter "%s" not found', $name));
    }

    public function getCache(): ?int
    {
        return self::CACHE;
    }

    public function getTotal(): ?int
    {
        return null;
    }

    public function getEvents(): array
    {
        return [];
    }

    public function getPagination(): ?array
    {
        $data = $this->getFilter(LimitFilter::class);

        if (null === $data) {
            return null;
        }

        $limit = array_shift($data);

        $data = $this->getFilter(OffsetFilter::class);

        if (null === $data) {
            return null;
        }

        $offset = array_shift($data);

        return [
            'limit' => (int) $limit,
            'offset' => (int) $offset,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters(): iterable
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(): iterable
    {
        return [];
    }

    public function getProperties(): array
    {
        return [];
    }

    public function setView(?string $view): static
    {
        $this->view = $view;

        return $this;
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function setWidget(Widget $widget): static
    {
        $this->widget = $widget;

        return $this;
    }

    public function getWidget(): Widget
    {
        return $this->widget;
    }

    /**
     * Return filter on custom condition.
     */
    protected function hasFilterOption(string $option): bool
    {
        $parameters = $this->getFilter(OptionFilter::class);

        if (null === $parameters) {
            return false;
        }

        return in_array($option, $parameters, true);
    }

    /**
     * Return function for filter with option.
     */
    protected function filterByOption(string $field): ?Func
    {
        $parameters = $this->getFilter(OptionFilter::class);

        if (null === $parameters) {
            return null;
        }

        $expr = new Expr();

        return $expr->in($field, $parameters);
    }

    /**
     * Return function to filter with field name.
     */
    protected function filterBy(string $name, string $field): Func
    {
        $expr = new Expr();

        $parameters = $this->getFilter($name);
        $parameterIds = [];

        foreach ($parameters as $parameter) {
            $parameterIds[] = $parameter->getId();
        }

        return $expr->in($field, $parameterIds);
    }
}
