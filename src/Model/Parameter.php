<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Spyck\VisualizationBundle\Controller\DashboardController;
use Symfony\Component\Serializer\Attribute as Serializer;

final class Parameter
{
    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private string $name;

    #[Serializer\Groups(groups: [DashboardController::GROUP_ITEM])]
    private string $field;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): static
    {
        $this->field = $field;

        return $this;
    }
}
