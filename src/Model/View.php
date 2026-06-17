<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Spyck\VisualizationBundle\Controller\DashboardController;
use Symfony\Component\Serializer\Attribute as Serializer;

final class View
{
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private string $code;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private string $name;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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
}
