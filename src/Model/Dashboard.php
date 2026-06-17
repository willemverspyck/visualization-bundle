<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use OpenApi\Attributes as OpenApi;
use Spyck\VisualizationBundle\Controller\DashboardController;
use Spyck\VisualizationBundle\Parameter\ParameterInterface;
use Spyck\VisualizationBundle\Request\RequestInterface;
use Spyck\VisualizationBundle\View\ViewInterface;
use Symfony\Component\Serializer\Attribute as Serializer;

final class Dashboard
{
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private ?int $id = null;

    private string $user;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private string $name;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private ?string $description = null;

    private ?string $copyright = null;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private ?string $url = null;

    #[OpenApi\Property(type: 'object', additionalProperties: new OpenApi\AdditionalProperties(type: 'string'))]
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private array $parameters = [];

    private array $parametersAsString = [];

    private array $parametersAsStringForSlug = [];

    #[OpenApi\Property(type: 'array', example: [ViewInterface::CSV, ViewInterface::XLSX], items: new OpenApi\Items(type: 'string'))]
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private array $views = [];

    #[OpenApi\Property(type: 'object', example: [RequestInterface::DATE_START => '2026-01-01', RequestInterface::DATE_END => '2026-01-07'], additionalProperties: new OpenApi\AdditionalProperties(type: 'string'))]
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private array $variables = [];

    /**
     * @var ArrayCollection<int, Block>
     */
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private ArrayCollection $blocks;

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): static
    {
        $this->user = $user;

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

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function setCopyright(?string $copyright): static
    {
        $this->copyright = $copyright;

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

    /**
     * @return list<ParameterInterface>
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

    /**
     * @return array<string, string>
     */
    public function getParametersAsString(): array
    {
        return $this->parametersAsString;
    }

    public function setParametersAsString(array $parameters): static
    {
        $this->parametersAsString = $parameters;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getParametersAsStringForSlug(): array
    {
        return $this->parametersAsStringForSlug;
    }

    public function setParametersAsStringForSlug(array $parametersForSlug): static
    {
        $this->parametersAsStringForSlug = $parametersForSlug;

        return $this;
    }

    /**
     * @return list<View>
     */
    public function getViews(): array
    {
        return $this->views;
    }

    public function setViews(array $views): static
    {
        $this->views = $views;

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

    public function addBlock(Block $block): static
    {
        $this->blocks->add($block);

        return $this;
    }

    public function clearBlocks(): void
    {
        $this->blocks->clear();
    }

    /**
     * @return ArrayCollection<Block>
     */
    public function getBlocks(): ArrayCollection
    {
        return $this->blocks;
    }

    public function removeBlock(Block $block): void
    {
        $this->blocks->removeElement($block);
    }

    public function __clone(): void
    {
        $this->blocks = new ArrayCollection();
    }
}
