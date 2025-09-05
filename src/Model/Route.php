<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Spyck\VisualizationBundle\Controller\DashboardController;
use Spyck\VisualizationBundle\Controller\MenuController;
use Symfony\Component\Serializer\Attribute as Serializer;

final class Route
{
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM, MenuController::GROUP_LIST])]
    private string $name;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM, MenuController::GROUP_LIST])]
    private string $url;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM, MenuController::GROUP_LIST])]
    private array $variables;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

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

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'url' => $this->getUrl(),
            'variables' => $this->getVariables(),
        ];
    }
}
