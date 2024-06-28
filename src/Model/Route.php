<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Controller\DashboardController;
use Spyck\VisualizationBundle\Controller\MenuController;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Route
{
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM, MenuController::GROUP_LIST])]
    private string $name;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM, MenuController::GROUP_LIST])]
    private string $url;

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
}
