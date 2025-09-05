<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OpenApi;
use Spyck\VisualizationBundle\Controller\DashboardController;
use Symfony\Component\Serializer\Attribute as Serializer;

final class Block
{
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private Dashboard $dashboard;

    private Widget $widget;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private string $name;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private ?string $description = null;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private ?string $descriptionEmpty = null;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private ?string $url = null;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private ?string $size = null;

    /**
     * @todo: Items can also be array
     */
    #[OpenApi\Property(type: 'array', items: new OpenApi\Items(type: 'string'))]
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private array $variables = [];

    /**
     * @var array<int, Filter>
     */
    #[OpenApi\Property(type: 'array', items: new OpenApi\Items(ref: new Model(type: Filter::class)))]
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private array $filters = [];

    /**
     * @var array<int, Parameter>
     */
    #[OpenApi\Property(type: 'array', items: new OpenApi\Items(ref: new Model(type: Parameter::class)))]
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private array $parameters = [];

    /**
     * @todo: Can be replaced with object
     */
    #[OpenApi\Property(type: 'string')]
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private array $downloads = [];

    /**
     * @todo: Can be replaced with object
     */
    #[OpenApi\Property(type: 'string')]
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private array $charts = [];

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private ?bool $filter = null;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private ?bool $filterView = null;

    public function getDashboard(): Dashboard
    {
        return $this->dashboard;
    }

    public function setDashboard(Dashboard $dashboard): static
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    public function getWidget(): Widget
    {
        return $this->widget;
    }

    public function setWidget(Widget $widget): static
    {
        $this->widget = $widget;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDescriptionEmpty(): ?string
    {
        return $this->descriptionEmpty;
    }

    public function setDescriptionEmpty(?string $descriptionEmpty): static
    {
        $this->descriptionEmpty = $descriptionEmpty;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * @return array<int, Filter>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return array<int, Parameter>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getDownloads(): array
    {
        return $this->downloads;
    }

    public function setDownloads(array $downloads): static
    {
        $this->downloads = $downloads;

        return $this;
    }

    public function getCharts(): array
    {
        return $this->charts;
    }

    public function setCharts(array $charts): static
    {
        $this->charts = $charts;

        return $this;
    }

    public function hasFilter(): ?bool
    {
        return $this->filter;
    }

    public function setFilter(?bool $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function hasFilterView(): ?bool
    {
        return $this->filterView;
    }

    public function setFilterView(?bool $filterView): static
    {
        $this->filterView = $filterView;

        return $this;
    }
}
